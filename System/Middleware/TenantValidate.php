<?php

namespace System\Config;

use PDO;
use System\App\Tenant;
use System\Concurrency\ThreadPool;
use System\Preload\SystemExc;

class TenantValidate
{
    private string $TenantDir;
    private array|null $Tenant;
    const WantToCreateAutoDB = true;
    private ?DB $DB = null;

    private const STEPS = [
        1 => 'Check Tenant Permissions',
        2 => 'Check Tenant Existence',
        3 => 'Verify Tenant Configuration',
        4 => 'Check and Create Tenant Directory Structure',
        5 => 'Check Directory Permissions',
        6 => 'Check Tenant DB Connection',
        7 => 'Check Tenant DB Name Existence',
        8 => 'Create Tenant Database if Not Exists',
        9 => 'Check Session Table Existence',
        10 => 'Check if Tenant is Deleted',
        11 => 'Check if Tenant is Active',
    ];

    private const CRITICAL_STEPS = [
        1 => 'Check Tenant Permissions',
        2 => 'Check Tenant Existence',
        3 => 'Verify Tenant Configuration',
        //4 => 'Check and Create Tenant Directory Structure',
        //5 => 'Check Directory Permissions',
        6 => 'Check Tenant DB Connection',
        //7 => 'Check Tenant DB Name Existence',
        //8 => 'Create Tenant Database if Not Exists',
        //9 => 'Check Session Table Existence',
        10 => 'Check this Tenant is Deleted',
        11 => 'Check this Tenant is Active or not',
    ];

    public function __construct(array $tenant = [], string $subDomain = '')
    {


        $this->TenantDir = TenantBaseDir;
        $this->Tenant = Tenant::Data();

        if (isset($this->Tenant['ID']) && $this->Tenant['ID'] > 0) {
            $_SERVER['Tenant']['ID'] = $this->Tenant['ID'];
            $_SERVER['Tenant']['Name'] = $this->Tenant['Name'];
            $this->init();
        } else {
            throw new SystemExc("Unauthorized access: Tenant does not exist or is not authorized.");
        }

    }

    private function init(): void
    {

        if ($this->Tenant['ComplatedStep'] == count(self::STEPS)) {
            $this->executeSteps(self::CRITICAL_STEPS, true);
            $this->DB = new DB(Credential::TenantDB(SubDomain(), $this->Tenant));
        } else {
            $this->executeSteps(self::STEPS);
        }


    }

    private function executeSteps(array $steps, bool $IsCritical = false): void
    {
        if ($IsCritical)
            $ThreadPool = new ThreadPool(5); // for example, max 5 concurrent tasks

        foreach ($steps as $step => $stepDescription) {
            try {
                $methodName = 'step' . $step;
                if (method_exists($this, $methodName)) {
                    $this->$methodName();
                    $IsCritical == true ?: $this->updateSetup($step);
                } else {
                    throw new SystemExc("Method {$methodName} does not exist.");
                }
            } catch (SystemExc $e) {
                // Use $step as an integer to access the description
                $stepDescription = self::STEPS[$step] ?? "Unknown step";
                throw new SystemExc("Validation failed at step $step ($stepDescription): " . $e->getMessage());
            }
        }
        if ($IsCritical)
            $ThreadPool->wait(); // Wait for all tasks to complete

    }

    private function step1(): void
    {
        if (!Tenant::Permission()) {
            throw new SystemExc("Multi-tenancy is not enabled.");
        }
    }

    private function step2(): void
    {
        if (empty($this->Tenant['ID'])) {
            throw new SystemExc("Tenant does not exist.");
        }
    }

    private function step3(): void
    {
        if (empty($this->Tenant['DB_Host']) || empty($this->Tenant['DB_Name']) || empty($this->Tenant['DB_User'])) {
            throw new SystemExc("Database credentials for tenant '{$this->Tenant['SubDomain']}' are incomplete.");
        }
    }

    private function step4(): void
    {
        $TenantDirPath = $this->TenantDir . '/' . $this->Tenant['ID'];
        if (!is_dir($TenantDirPath) && !mkdir($TenantDirPath, 0755, true)) {
            throw new SystemExc("Failed to create tenant directory for '{$this->Tenant['SubDomain']}'.");
        }

        foreach (['AccessLog', 'ErrorLog'] as $logDir) {
            $logDirPath = $TenantDirPath . '/' . $logDir;
            if (!is_dir($logDirPath) && !mkdir($logDirPath, 0755, true)) {
                throw new SystemExc("Failed to create directory '$logDir' for tenant '{$this->Tenant['SubDomain']}'.");
            }
        }
    }

    private function step5(): void
    {
        $TenantDirPath = $this->TenantDir . '/' . $this->Tenant['ID'];
        if (!is_readable($TenantDirPath) || !is_writable($TenantDirPath)) {
            throw new SystemExc("Permissions for tenant directory '{$this->Tenant['SubDomain']}' are not set correctly.");
        }
    }

    private function step6(): void
    {
        if (!$this->checkDatabaseConnection()) {
            throw new SystemExc("Database connection failed.");
        }
    }

    private function step7(): void
    {
        if (!$this->DB->CheckDBExisted($this->Tenant['DB_Name']) && !self::WantToCreateAutoDB) {
            throw new SystemExc("Database '{$this->Tenant['DB_Name']}' does not exist.");
        }
    }

    private function step8(): void
    {
        $this->createDatabaseIfNotExists($this->Tenant['DB_Name']);
    }

    private function step9(): void
    {
        if (!$this->DB->CheckTableExisted('Sessions')) {
            $this->createTableInDefaultDB('Sessions', $this->getSessionTableSchema());
        }
    }

    private function step10(): void
    {
        if ($this->Tenant['Deleted'] > 0) {
            throw new SystemExc('This user is no longer associated with the domain.', 404);
        }
    }

    private function step11(): void
    {
        if ($this->Tenant['Status'] != 1) {
            throw new SystemExc('This user is inactive', 404);
        }
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            $credential = [
                'DB_Type' => strtolower($this->Tenant['DB_Type']),
                'DB_Host' => $this->Tenant['DB_Host'],
                'DB_Name' => $this->Tenant['DB_Name'],
                'DB_User' => $this->Tenant['DB_User'],
                'DB_Password' => $this->Tenant['DB_Password'],
            ];

            //$this->DB = new DB($credential);
            $DBConnection = new PDO("{$credential['DB_Type']}:host={$credential['DB_Host']};", $credential['DB_User'], $credential['DB_Password']);
            $DBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $DBConnection->query("SELECT 1");
            $this->DB = new DB(Credential::TenantDB(SubDomain(), $this->Tenant));

            return true;
        } catch (\Exception $e) {
            throw new SystemExc("Database connection failed: " . $e->getMessage());
        }
    }

    private function createDatabaseIfNotExists(string $DbName): bool
    {
        if (!$this->DB->CheckDBExisted($DbName)) {
            if (self::WantToCreateAutoDB) {
                $this->DB->CreateDB($DbName);
                if (!$this->DB->CheckDBExisted($DbName)) {
                    throw new SystemExc('Failed to create database. Please check server configuration.');
                }
            } else {
                throw new SystemExc("$DbName database not found in the connected instance.");
            }
        }
        $this->DB = new DB(Credential::TenantDB(SubDomain(), $this->Tenant));
        return true;
    }

    private function createTableInDefaultDB(string $TableName, array $Schema): bool
    {
        if (!$this->DB->CheckTableExisted($TableName)) {
            $this->DB->CreateTable($TableName, $Schema);
        }
        return true;
    }

    private function getSessionTableSchema(): array
    {
        return [
            'session_id varchar(32) NOT NULL',
            'modified timestamp NOT NULL DEFAULT current_timestamp()',
            'data blob DEFAULT NULL',
            'IP varchar(100) DEFAULT NULL',
            'Browser text DEFAULT NULL',
            'PRIMARY KEY (session_id)'
        ];
    }

    private function updateSetup(int $step): void
    {
        Tenant::Update(['ComplatedStep' => $step], $this->Tenant['ID']);
    }

    public function getData()
    {
        return $this->Tenant;
    }
}

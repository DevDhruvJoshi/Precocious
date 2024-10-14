<?php

namespace System\Middleware;

use PDO;
use Symfony\Component\Dotenv\Dotenv;
use System\App\Tenant;
use System\Config\Credential;
use System\Config\DB;
use System\Preload\SystemExc;

class SystemValidate
{
    const OwnerDBPrefix = 'Owner';
    const WantToCreateAutoDB = true;
    private string $OwnerDir;
    private string $TenantDir;
    private string $StatusFile;
    private ?DB $Db = null;
    private ?DB $OwnerDb = null;
    private bool $DbInitialized = false;

    private const CRITICAL_STEPS = [
        1, // Check Database Credentials
        2, // Check Database Connection
        11 // Check and Insert Admin Tenant
    ];


    private const STEPS = [
        1 => 'Check Database Credentials',
        2 => 'Check Database Connection',
        3 => 'Check and Create Default Database',
        4 => 'Check Directory Permissions',
        5 => 'Check and Create Owner Directory',
        6 => 'Check and Create Status File',
        7 => 'Check and Create Env File',
        8 => 'Create Owner Database If Not Exists',
        9 => 'Setup Database',
        10 => 'Create Tables If Not Exists',
        11 => 'Check and Insert Admin Tenant',
        12 => 'Check Tenant Directory Structure',
    ];

    public function __construct()
    {
        $this->OwnerDir = OwnerBaseDir;
        $this->TenantDir = TenantBaseDir;
        $this->StatusFile = env('MultiTenancy') ? 'MultiTenancy.proto' : 'NonMultiTenancy.proto';
        $this->Db = new DB(Credential::DefaultDB());
        $this->OwnerDb = file_exists(rtrim($this->OwnerDir, '/') . '/.env') ? new DB(Credential::OwnerDB()) : null;
        $this->Init();
    }

    private function Init(): void
    {
        if ($this->CheckStatusFile()) {
            $this->CheckCriticalSteps(); // Check critical steps after all steps are completed
            return;
        }

        $this->ExecuteSteps();
        $this->UpdateStatusFile(count(self::STEPS));

    }
    private function CheckCriticalSteps(): void
    {
        foreach (self::CRITICAL_STEPS as $step) {
            $methodName = 'CheckStep' . $step;
            if (method_exists($this, $methodName)) {
                try {
                    $this->$methodName();
                } catch (SystemExc $e) {
                    throw new SystemExc("Critical check failed for step $step: " . $e->getMessage());
                }
            }
        }
    }

    private function CheckStep1(): void
    {
        $this->CheckDatabaseCredentials();
    }

    private function CheckStep2(): void
    {
        // Check the connection to the default database
        $this->CheckDatabaseConnection();

        // If tenant permissions are enabled
        if (Tenant::Permission() === true) {
            // Check the connection to the owner database
            $this->CheckDatabaseConnection(Credential::OwnerDB());

            // Verify if the owner database exists
            if (!$this->OwnerDb->CheckDBExisted(env('OwnerDB_Name'))) {
                throw new SystemExc(env('OwnerDB_Name') . " database (owner) not found in the connected instance.");
            }
        }

        // No need to return anything, as the method is void
    }

    private function CheckStep11(): void
    {
        $this->CheckAndInsertAdminTenant();
    }

    private function ExecuteSteps(): void
    {
        $this->CheckDatabaseCredentials();
        $this->CheckDatabaseConnection();

        foreach (self::STEPS as $Step => $Description) {
            $MethodName = 'Step' . $Step;
            if (method_exists($this, $MethodName)) {
                try {
                    $this->$MethodName();
                    $this->UpdateStatusFile($Step); // Update status after each step
                } catch (SystemExc $e) {
                    throw new SystemExc("Validation failed at step $Step ($Description): " . $e->getMessage());
                }
            }
        }
    }

    private function Step3(): bool
    {
        return $this->CreateDatabaseIfNotExists(env('DB_Name'));
    }

    private function Step4(): bool
    {
        return $this->CheckDirectoryPermissions();
    }

    private function Step5(): bool
    {
        return $this->CheckAndCreateOwnerDirectory();
    }

    private function Step6(): bool
    {
        return $this->CheckAndCreateStatusFile();
    }

    private function Step7(): bool
    {
        return $this->EnsureEnvFileExists();
    }

    private function Step8(): bool
    {
        return $this->CreateDatabaseIfNotExists(self::OwnerDBPrefix . env('DB_Name'));
    }

    private function Step9(): bool
    {
        return $this->SetupDatabase();
    }

    private function Step10(): bool
    {
        return $this->CreateTableIfNotExists('Sessions', $this->GetSessionTableSchema()) &&
            $this->CreateTableInDefaultDB('Sessions', $this->GetSessionTableSchema());
    }

    private function Step11(): bool
    {
        return $this->CheckAndInsertAdminTenant();
    }

    private function Step12(): bool
    {
        return $this->CheckTenantDirectoryStructure();
    }

    private function CheckStatusFile(): bool
    {
        $FilePath = $this->OwnerDir . '/' . $this->StatusFile;

        if (file_exists($FilePath)) {
            $CompletedSteps = file_get_contents($FilePath); // Get the content directly as a string
            return trim($CompletedSteps) === (string) count(self::STEPS); // Compare trimmed content with expected count
        }

        return false;
    }

    private function EnsureEnvFileExists(): bool
    {
        $EnvFilePath = rtrim($this->OwnerDir, '/') . '/.env';

        if (!file_exists($EnvFilePath)) {
            $this->CreateEnvFile($EnvFilePath);
            $dotenv = new Dotenv();
            $dotenv->load($EnvFilePath);
            $this->OwnerDb = new DB(Credential::OwnerDB(), true);
        }

        return parse_ini_file($EnvFilePath) !== false;
    }

    private function CreateEnvFile(string $EnvFilePath): void
    {
        $envConfig = [
            'OwnerSubDomain' => strtolower('owner'),
            'OwnerDB_Type' => env('DB_Type'),
            'OwnerDB_Host' => env('DB_Host'),
            'OwnerDB_Name' => self::OwnerDBPrefix . env('DB_Name'),
            'OwnerDB_User' => env('DB_User'),
            'OwnerDB_Password' => env('DB_Password'),
        ];

        foreach ($envConfig as $key => $value) {
            if (empty($value) && $key !== 'OwnerDB_Password') {
                throw new SystemExc("Environment variable $key is not set.");
            }
        }

        $Content = "# Owner DB Config\n";
        foreach ($envConfig as $key => $value) {
            $Content .= "$key=\"$value\"\n";
        }

        file_put_contents($EnvFilePath, $Content);
    }

    private function CheckDirectoryPermissions(): bool
    {
        foreach ([$this->OwnerDir, $this->TenantDir] as $Dir) {
            if (!is_dir($Dir)) {
                if (!mkdir($Dir, 0755, true)) {
                    throw new SystemExc("Failed to create directory $Dir.");
                }
            }

            if (!is_writable($Dir)) {
                throw new SystemExc("Directory $Dir is not writable.");
            }
        }
        return true;
    }

    private function CheckAndCreateOwnerDirectory(): bool
    {
        if (!is_dir($this->OwnerDir)) {
            if (!mkdir($this->OwnerDir, 0777, true)) {
                throw new SystemExc("Failed to create owner directory.");
            }
        }
        return true;
    }

    private function CheckAndCreateStatusFile(): bool
    {
        $FilePath = $this->OwnerDir . '/' . $this->StatusFile;
        if (!file_exists($FilePath)) {
            file_put_contents($FilePath, ""); // Create an empty file
        }
        return true;
    }

    private function CheckDatabaseCredentials(): bool
    {
        $EnvPath = Root . '.env';
        if (!file_exists($EnvPath)) {
            throw new SystemExc(".env file not found at $EnvPath. Please ensure it's correctly placed.");
        }

        if (empty(env('DB_Host')) || empty(env('DB_Name')) || empty(env('DB_User'))) {
            throw new SystemExc("Database credentials are incomplete.");
        }
        return true;
    }

    private function CheckDatabaseConnection(array $Credential = null): bool
    {
        try {
            $Credential = !empty($Credential) ? $Credential : Credential::DefaultDB();
            $DBType = strtolower($Credential['DB_Type']);
            $DBHost = $Credential['DB_Host'];
            $DBUser = $Credential['DB_User'];
            $DBPassword = $Credential['DB_Password'];

            $DBConnection = new PDO("$DBType:host=$DBHost;", $DBUser, $DBPassword);
            $DBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $DBConnection->query("SELECT 1");
            return true;
        } catch (\Exception $e) {
            throw new SystemExc("Database connection failed: " . $e->getMessage());
        }
    }

    private function SetupDatabase(): bool
    {
        $this->CreateTableIfNotExists('Sessions', $this->GetSessionTableSchema());
        $this->CreateTableIfNotExists('Tenants', $this->GetTenantTableSchema());
        return $this->CheckAndInsertAdminTenant();
    }

    private function CreateDatabaseIfNotExists(string $DbName): bool
    {
        if (!$this->Db->CheckDBExisted($DbName) && !self::WantToCreateAutoDB) {
            throw new SystemExc("$DbName database not found in the connected instance.");
        }

        if (!$this->Db->CheckDBExisted($DbName)) {
            $this->Db->CreateDB($DbName);
            $this->Db = new DB(Credential::DefaultDB(), self::WantToCreateAutoDB);

            if (!$this->Db->CheckDBExisted($DbName)) {
                throw new SystemExc('Failed to create database. Please check server configuration.');
            }
        }
        return true;
    }

    private function CreateTableIfNotExists(string $TableName, array $Schema): bool
    {
        if (!$this->OwnerDb->CheckTableExisted($TableName)) {
            $this->OwnerDb->CreateTable($TableName, $Schema);
        }
        return true;
    }

    private function CreateTableInDefaultDB(string $TableName, array $Schema): bool
    {
        if (!$this->Db->CheckTableExisted($TableName)) {
            $this->Db->CreateTable($TableName, $Schema);
        }
        return true;
    }

    private function GetSessionTableSchema(): array
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

    private function GetTenantTableSchema(): array
    {
        return [
            'ID int(11) NOT NULL AUTO_INCREMENT',
            'Name varchar(255) NOT NULL',
            'SubDomain varchar(255) NOT NULL',
            'Status int(11) DEFAULT 0',
            'DB_Type varchar(255) DEFAULT NULL',
            'DB_Host varchar(255) DEFAULT NULL',
            'DB_Name varchar(255) DEFAULT NULL',
            'DB_User varchar(255) DEFAULT NULL',
            'DB_Password text DEFAULT NULL',
            'ComplatedStep int(1) DEFAULT 0',
            'Deleted int(1) DEFAULT 0',
            'DeletedAt int(11) DEFAULT NULL',
            'PRIMARY KEY (ID)'
        ];
    }

    private function CheckAndInsertAdminTenant(): bool
    {
        $AdminTenantData = [
            'Name' => 'Owner Management',
            'SubDomain' => 'owner',
            'Status' => 1,
            'DB_Type' => 'MySql',
            'DB_Host' => 'localhost',
            'DB_Name' => self::OwnerDBPrefix . env('DB_Name'),
            'DB_User' => 'root',
            'DB_Password' => '',
            'Deleted' => 0
        ];

        if (!$this->OwnerDb->CheckTableExisted('Tenants')) {
            throw new SystemExc("Tenants table does not exist.");
        }

        $ExistingTenant = $this->OwnerDb->Select('Tenants', ['ID'], ['ID' => 1]);
        if ($ExistingTenant) {
            return true;
        } else {
            $InsertId = $this->OwnerDb->Insert('Tenants', $AdminTenantData);
            if (!$InsertId) {
                throw new SystemExc("Failed to insert admin tenant.");
            }
        }

        return true;
    }

    private function CheckTenantDirectoryStructure($ID = null): bool
    {
        // $TenantBase = new System\App\Tenant\Base(); namespace issue resolved pendig
        $this->CreateTenantDirectoryStructure(0); // is for base domain
        $this->CreateTenantDirectoryStructure(1); // is for admin subdomain
        $this->CreateTenantDirectoryStructure('Unauthorized'); // is for unauthorized tenantsubdomain log
        return true;

    }
    function CreateTenantDirectoryStructure($ID = null): bool
    {
        $tenantDirPath = TenantBaseDir . '/' . ($ID != null ? $ID : $_SERVER['Tenant']['ID']);
        if (!is_dir($tenantDirPath)) {
            if (!mkdir($tenantDirPath, 0777, true)) {
                throw new SystemExc("Failed to create tenant($ID) directory structure.");
            }
        }

        foreach (['AccessLog', 'ErrorLog'] as $logDir) {
            $logDirPath = $tenantDirPath . '/' . $logDir;
            if (!is_dir($logDirPath)) {
                if (!mkdir($logDirPath, 0777, true)) {
                    throw new SystemExc("Failed to create $logDir directory. for Tenant ($ID)");
                }
            }
        }

        return true;
    }
    private function UpdateStatusFile(int $Step): void
    {
        $FilePath = $this->OwnerDir . '/' . $this->StatusFile;

        // Create directory if it doesn't exist
        if (!is_dir($this->OwnerDir)) {
            mkdir($this->OwnerDir, 0777, true); // 0777 is the permission level
        }

        file_put_contents($FilePath, (string) $Step);
    }
}

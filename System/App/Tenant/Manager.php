<?php

namespace System\App\Tenant;

use PDO;
use System\Preload\SystemExc;

class Manager {
    private $tenantId;
    private $stepsFile;
    private $steps;
    private $validator;
    private $baseDir;

    public function __construct($tenantId, SystemValidator $validator) {
        $this->tenantId = $tenantId;
        $this->validator = $validator;
        $this->baseDir = __DIR__ . "/../Tenant/{$tenantId}";
        $this->stepsFile = "{$this->baseDir}/steps.json";
        $this->steps = [
            'subdomain_check' => false,
            'virtual_host_check' => false,
            'database_creation' => false,
            'sql_import' => false,
            'asset_directory_creation' => false,
            'tenant_insertion' => false,
        ];

        $this->checkPermissions();
        $this->initializeSteps();
    }

    private function checkPermissions() {
        if (!is_dir($this->baseDir)) {
            mkdir($this->baseDir, 0755, true);
        }

        if (!is_writable($this->baseDir)) {
            throw new SystemExc("Directory {$this->baseDir} is not writable. Please set appropriate permissions.");
        }
    }

    private function initializeSteps() {
        if (file_exists($this->stepsFile)) {
            $this->steps = json_decode(file_get_contents($this->stepsFile), true);
        } else {
            file_put_contents($this->stepsFile, json_encode($this->steps));
        }
    }

    public function processTenant($tenantName, $subDomain) {
        if (!$this->steps['subdomain_check']) {
            if (!$this->validator->checkSubdomain($subDomain)) {
                return "Tenant with this subdomain already exists.";
            }
            $this->steps['subdomain_check'] = true;
        }

        if (!$this->steps['virtual_host_check']) {
            $this->checkVirtualHost($subDomain);
        }

        if (!$this->steps['database_creation']) {
            $this->createDatabase($tenantName);
        }

        if (!$this->steps['sql_import']) {
            $this->importSql($tenantName);
        }

        if (!$this->steps['asset_directory_creation']) {
            $this->createAssetDirectory($subDomain);
        }

        if (!$this->steps['tenant_insertion']) {
            $this->insertTenant($tenantName, $subDomain);
        }

        file_put_contents($this->stepsFile, json_encode($this->steps));

        return "Process completed for tenant: {$tenantName}.";
    }

    private function checkVirtualHost($subDomain) {
        $virtualHost = "/var/www/$subDomain"; // Example path
        if (!is_dir($virtualHost)) {
            mkdir($virtualHost, 0755, true);
            $this->steps['virtual_host_check'] = true;
        }
    }

    private function createDatabase($tenantName) {
        $dbName = strtolower($tenantName) . "_db"; // Custom DB name
        $pdo = new PDO("mysql:host=" . getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'));
        $pdo->exec("CREATE DATABASE `$dbName`");
        $this->steps['database_creation'] = true;
    }

    private function importSql($tenantName) {
        $dbName = strtolower($tenantName) . "_db";
        $sql = file_get_contents(__DIR__ . '/example.sql'); // Load SQL file
        $pdo = new PDO("mysql:host=" . getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'));
        $pdo->exec("USE `$dbName`");
        $sqlQueries = explode(';', $sql);
        foreach ($sqlQueries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $pdo->exec($query);
            }
        }
        $this->steps['sql_import'] = true;
    }

    private function createAssetDirectory($subDomain) {
        $assetsDir = "{$this->baseDir}/assets";
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
            $this->steps['asset_directory_creation'] = true;
        }
    }

    private function insertTenant($tenantName, $subDomain) {
        $dbName = strtolower($tenantName) . "_db";
        $pdo = new PDO("mysql:host=" . getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'));
        $insertStmt = $pdo->prepare("INSERT INTO Tenants (Name, SubDomain, DB_Name) VALUES (:name, :subDomain, :dbName)");
        $insertStmt->execute(['name' => $tenantName, 'subDomain' => $subDomain, 'dbName' => $dbName]);
        $this->steps['tenant_insertion'] = true;
    }

    public function getPendingSteps() {
        $pendingSteps = [];
        foreach ($this->steps as $step => $completed) {
            if (!$completed) {
                $pendingSteps[] = $step;
            }
        }
        return $pendingSteps;
    }
}

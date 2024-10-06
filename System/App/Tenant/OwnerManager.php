<?php

namespace System\App\Tenant;

use System\Preload\SystemExc;

class OwnerManager {
    private $pdo;
    private $validator;

    public function __construct(SystemValidator $validator) {
        $this->validator = $validator;
        $this->initializeDatabaseConnection(); // Initialize database connection
    }

    private function initializeDatabaseConnection() {
        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');
        $this->pdo = new \PDO("mysql:host=$host", $user, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function processOwner() {
        $ownerId = 0; // Owner ID is always 0
        $ownerDir = __DIR__ . "/../Tenant/{$ownerId}";

        if (!is_dir($ownerDir)) {
            mkdir($ownerDir, 0755, true);
        }

        if (!is_writable($ownerDir)) {
            throw new SystemExc("Directory {$ownerDir} is not writable. Please set appropriate permissions.");
        }

        // Ensure owner database and table exist
        $ownerDbName = 'owner_db'; // Owner database name
        $this->createDatabaseIfNotExists($ownerDbName);

        // Check if the owner entry exists
        if (!$this->validator->checkOwnerExists()) {
            $insertStmt = $this->pdo->prepare("INSERT INTO `$ownerDbName`.`Tenants` (ID, Name, SubDomain) VALUES (:id, 'Owner', 'owner')");
            $insertStmt->execute(['id' => $ownerId]);
        }

        return true; // Owner process completed
    }

    private function createDatabaseIfNotExists($dbName) {
        $stmt = $this->pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbName");
        $stmt->execute(['dbName' => $dbName]);

        if ($stmt->rowCount() === 0) {
            // Database does not exist, create it
            $this->pdo->exec("CREATE DATABASE `$dbName`");
        }
    }
    
    private function createOwnerTenantTable($dbName) {
        $createTableSQL = "
        CREATE TABLE `Tenants` (
          `ID` int(11) NOT NULL AUTO_INCREMENT,
          `Name` varchar(255) NOT NULL,
          `SubDomain` varchar(255) NOT NULL,
          `Status` int(11) DEFAULT 0,
          `DB_Type` varchar(255) DEFAULT NULL,
          `DB_Host` varchar(255) DEFAULT NULL,
          `DB_Name` varchar(255) DEFAULT NULL,
          `DB_User` varchar(255) DEFAULT NULL,
          `DB_Password` text DEFAULT NULL,
          `Deleted` int(1) DEFAULT 0,
          `DeletedAt` int(11) DEFAULT NULL,
          PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";

        $this->pdo->exec("USE `$dbName`");
        $this->pdo->exec($createTableSQL);
    }
}

<?php

namespace System\App\Tenant;

use System\Config\DB;

class SystemValidator {
    private $db;

    public function __construct(DB $db) {
        $this->db = $db;
    }

    public function checkSubdomain($subDomain) {
        return $this->db->Select('Tenants', ['*'], ['SubDomain' => $subDomain]) === []; // True if subdomain does not exist
    }

    public function checkOwnerExists(string $ownerDbName) {
        dd('alksd');
        $this->createDatabaseIfNotExists($ownerDbName); // Ensure owner DB exists

        // Check if the Tenants table exists in the owner database
        $this->db->Connection->exec("USE `$ownerDbName`");
        if (!$this->db->CheckTableExisted('Tenants')) {
            // Tenants table does not exist, create it
            $this->createOwnerTenantTable($ownerDbName);
        }

        return true; // Owner DB and table checks passed
    }

    private function createDatabaseIfNotExists($dbName) {
        if (!$this->db->CheckDBExisted($dbName)) {
            $this->db->CreateDB($dbName);
            $this->db = new DB();
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

        $this->db->Connection->exec("USE `$dbName`");
        $this->db->Connection->exec($createTableSQL);
    }
}
?>

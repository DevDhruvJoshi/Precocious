<?php
namespace System\App\Tenant;

use PDO;

class SystemValidator {
    private $pdo;

    public function __construct() {
        $this->initializeDatabaseConnection();
    }

    private function initializeDatabaseConnection() {
        $type = strtolower(env('DB_Type'));
        $host = env('DB_Host');
        $user = env('DB_User');
        $password = env('DB_Password');

        $this->pdo = new PDO($type.":host=$host", $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function checkSubdomain($subDomain) {
        $stmt = $this->pdo->prepare("SELECT * FROM Tenants WHERE SubDomain = :subDomain");
        $stmt->execute(['subDomain' => $subDomain]);
        return $stmt->rowCount() === 0; // True if subdomain does not exist
    }

    public function checkOwnerExists() {
        $ownerDbName = 'owner_db'; // Owner ka database naam
    $this->createDatabaseIfNotExists($ownerDbName); // Ensure owner DB exists

    // Check if the Tenants table exists in the owner database
    $this->pdo->exec("USE `$ownerDbName`");
    $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'Tenants'");
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        // Tenants table does not exist, create it
        $this->createOwnerTenantTable($ownerDbName);
    }

    return true; // Owner DB and table checks passed
    }
}

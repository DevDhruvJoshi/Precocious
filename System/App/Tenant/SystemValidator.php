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
        $stmt = $this->pdo->prepare("SELECT * FROM Tenants WHERE ID = :id");
        $stmt->execute(['id' => 0]);
        return $stmt->rowCount() > 0; // True if owner exists
    }
}

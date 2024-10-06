<?php
namespace System\App\Tenant;

use PDO;

class SystemValidator {
    private $pdo;

    public function __construct() {
        $this->initializeDatabaseConnection();
    }

    private function initializeDatabaseConnection() {
        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');

        $this->pdo = new PDO("mysql:host=$host", $user, $password);
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

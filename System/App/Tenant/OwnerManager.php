<?php

namespace System\App\Tenant;

use System\Preload\SystemExc;

class OwnerManager {
    private $validator;

    public function __construct(SystemValidator $validator) {
        $this->validator = $validator;
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

        if (!$this->validator->checkOwnerExists()) {
            $insertStmt = $this->pdo->prepare("INSERT INTO Tenants (ID, Name, SubDomain) VALUES (:id, 'Owner', 'owner')");
            $insertStmt->execute(['id' => $ownerId]);
        }

        return true; // Owner process completed
    }
}

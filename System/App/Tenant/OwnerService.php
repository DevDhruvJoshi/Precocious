<?php

namespace System\App\Service;

use System\App\Tenant\SystemValidator;
use System\Config\DB;
use System\Preload\SystemExc;

class OwnerService {
    private $validator;
    private $db;

    public function __construct(SystemValidator $validator, DB $db) {
        $this->validator = $validator;
        $this->db = $db;
    }

    public function ensureOwnerExists($ownerName, $subDomain) {
        if (!$this->validator->checkOwnerExists(env('DB_Name'))) {
            $this->createOwner($ownerName, $subDomain);
        }
    }

    private function createOwner($ownerName, $subDomain) {
        // Create the owner in the database
        dd('        // Create the owner in the database');
        $this->db->Insert('Tenants', [
            'ID' => 1,
            'Name' => $ownerName,
            'SubDomain' => $subDomain
        ]);
    }
}
?>

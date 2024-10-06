<?php

namespace System\App\Service;

use System\App\Tenant\SystemValidator;
use System\Config\DB;
use System\Preload\SystemExc;

class TenantService {
    private $validator;
    private $db;

    public function __construct(SystemValidator $validator, DB $db) {
        $this->validator = $validator;
        $this->db = $db;
    }

    public function processTenant($tenantName, $subDomain) {
        // Check and validate subdomain
        if (!$this->validator->checkSubdomain($subDomain)) {
            throw new SystemExc("Tenant with this subdomain already exists.");
        }

        // Create database and tenant
        $this->createDatabase($tenantName);
        $this->createTenantEntry($tenantName, $subDomain);
    }

    private function createDatabase($tenantName) {
        $dbName = strtolower($tenantName) . "_db";
        if (!$this->db->CheckDBExisted($dbName)) {
            $this->db->CreateDB($dbName);
        }
    }

    private function createTenantEntry($tenantName, $subDomain) {
        // Insert tenant into the Tenants table
        $this->db->Insert('Tenants', [
            'ID' => null, // Auto increment
            'Name' => $tenantName,
            'SubDomain' => $subDomain
        ]);
    }
}
?>

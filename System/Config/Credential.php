<?php

namespace System\Config;

use System\App\Tenant;
use System\Preload\SystemExc;

/**
 * Class Credential
 * Handles database configuration retrieval for different database contexts
 * (Default, Owner, and Tenant databases).
 * 
 * Author: Dhruv Joshi
 * Created on: 27-July-2024
 */
class Credential
{
    /**
     * Retrieves the default database configuration.
     *
     * @return array An associative array containing the default database configurations.
     */
    public static function DefaultDB(): array
    {
        return [
            'DB_Type' => env('DB_Type') ?: '',
            'DB_Host' => env('DB_Host') ?: '',
            'DB_Name' => env('DB_Name') ?: '',
            'DB_User' => env('DB_User') ?: '',
            'DB_Password' => env('DB_Password') ?: '',
            'ID' => 0,
        ];
    }

    /**
     * Retrieves the owner database configuration.
     *
     * @return array An associative array containing the owner database configurations.
     */
    public static function OwnerDB(): array
    {
        return [
            'DB_Type' => env('OwnerDB_Type') ?: '',
            'DB_Host' => env('OwnerDB_Host') ?: '',
            'DB_Name' => env('OwnerDB_Name') ?: '',
            'DB_User' => env('OwnerDB_User') ?: '',
            'DB_Password' => env('OwnerDB_Password') ?: '',
            'ID' => 1,
        ];
    }

    /**
     * Retrieves the tenant database configuration based on the provided or resolved subdomain.
     *
     * @param string|null $SubDomain The subdomain for which to retrieve the tenant database config.
     * @return array An associative array containing the tenant database configurations.
     */
    public static function TenantDB($SubDomain = null, array $Tenant = []): array
    {
        // Resolve the subdomain if not provided
        $Tenant = empty($Tenant) ? Tenant::Data($SubDomain) : $Tenant;

        return [
            'DB_Type' => $Tenant['DB_Type'] ?? '',
            'DB_Host' => $Tenant['DB_Host'] ?? '',
            'DB_Name' => $Tenant['DB_Name'] ?? '',
            'DB_User' => $Tenant['DB_User'] ?? '',
            'DB_Password' => $Tenant['DB_Password'] ?? '',
            'ID' => $Tenant['ID'],

        ];
    }

    /**
     * Retrieves the appropriate database configuration based on the tenant's permission and subdomain.
     *
     * @param string|null $SubDomain The subdomain for which to retrieve the database config.
     * @return array An associative array containing the appropriate database configurations.
     */
    public static function DB($SubDomain = null): array
    {
        $SubDomain = $SubDomain ?: SubDomain();

        if (Tenant::Permission() == true && !empty($SubDomain)) {
            return ($SubDomain === env('OwnerSubDomain')) ? self::OwnerDB() : self::TenantDB($SubDomain);
        } // elseif(Tenant::Permission() != true && !empty($SubDomain) )


        return self::DefaultDB();
    }

}

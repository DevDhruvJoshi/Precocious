<?php

namespace System\App;
use System\Config\DB;
use System\App\Tenant\SystemValidator;
use System\App\Service\OwnerService;
use System\App\Service\TenantService;
use System\Preload\SystemExc;

class Tenant extends \System\App\Tenant\Base
{

    //class Tenant extends \System\Config\DB{

    public static $_Table = 'Tenants';
    public static $_ID = 'ID';
    public static $_Trash = 'Deleted'; // Int 1 default 0
    public static $_TrashAt = 'DeletedAt'; // Int 11 default null

    function __construct(...$argu)
    {

    }

    static function Permission()
    {
        return env('MultiTenancy') == 1 ? true : false;
    }

    static function Install($ID = 0, $ErrorCode = null)
    { // pending all code intall setup one time
        $R = [];
        $TenantDir = TenantBaseDir . ($ID) . DS;
        if ($ErrorCode == 12 || $ErrorCode === 0) // Tenant Dir Not Found Error code 12 
            $R['CreateTenantDir'] = is_dir($TenantDir) ? 'Already Exiest' : mkdir($TenantDir, 0777);
        if ($ErrorCode == 13 || $ErrorCode === 0) // TenantLog Dir Not Found Error code 13 
            $R['CreateTenantErrorLogDir'] = is_dir($TenantDir . 'ErrorLog') ? 'Already Exiest' : mkdir($TenantDir . 'ErrorLog', 0777);
        if ($ErrorCode == 14 || $ErrorCode === 0) // Tenant Access LogDir Not Found Error code 14 
            $R['CreateTenantAccessLogDir'] = is_dir($TenantDir . 'AccessLog') ? 'Already Exiest' : mkdir($TenantDir . 'AccessLog', 0777);
        return $R;
    }
    public static function Init()
    {
        // Initialize your DB instance
        $db = new DB();
        $validator = new SystemValidator($db);
        $ownerService = new OwnerService($validator, $db);
        $tenantService = new TenantService($validator, $db);

        // Here you can run any setup code as needed
        // For example, setting up the owner and tenant
        try {
            // Setup owner (example)
            $ownerName = 'Owner Name';
            $ownerSubDomain = env('OwnerSubDomain');
            $ownerService->ensureOwnerExists($ownerName, $ownerSubDomain);
            echo "Owner setup completed successfully.\n";

            // Setup tenant (example)
            if (!empty(SubDomain()) && 1 == 2) {
                $tenantService->processTenant('Tenant1','user1');
                echo "Tenant setup completed successfully.\n";
            }

        } catch (SystemExc $e) {
            echo "Tenant Setup failed: " . $e->getMessage() . "\n";
        }
    }

    static function DetailsBySubDomain(string $SubDomain = '')
    {
        if (!empty($SubDomain)) {
            if (!empty($T = Tenant::SelectOnes(['*'], ['SubDomain' => $SubDomain]))) {
                //if (!empty($T = Tenant::Select([], 'SubDomain = "' . $SubDomain . '"'))) {
                return $T;
            }
        }
    }

    static function IsVerified(string $SubDomain = '')
    {
        try {
            $Tenant = self::DetailsBySubDomain(($SubDomain ?: SubDomain()) ?: '');
            if (!empty($Tenant)) {
                if ($Tenant['Deleted'] != 1) {
                    if ($Tenant['Status'] == 1) {
                        if (!empty($Tenant['DB_Type']) && !empty($Tenant['DB_Host'])) {
                            if (!empty($Tenant['DB_Name']) && !empty($Tenant['DB_User'])) {
                                if (is_dir(TenantBaseDir . $Tenant['ID'])) {
                                    $_SERVER['Tenant']['ID'] = $Tenant['ID'];
                                    $_SERVER['Tenant']['Name'] = $Tenant['Name'];
                                    require_once Config . 'Tenant.php';
                                    return true;
                                } else
                                    throw new \SystemExc('This Tenant is not configure, Some Directory not found', 12);
                            } else
                                throw new \SystemExc('Need to configure Database credencial', 404);
                        } else
                            throw new \SystemExc('Need to configure Database', 404);
                    } else
                        throw new \SystemExc('This user is inactive', 404);
                } else
                    throw new \SystemExc('This user is no longer associated with the domain.', 404);
            } else
                throw new \SystemExc('Invalid URL - Not found', 404);
        } catch (\SystemExc $E) {
            $E->Response($E);
            //throw new Exc($E->getMessage(), $E->getCode(), $E);
            //Response($E->getCode(), $E->getMessage(), isset($D) ? $D : [], $E);
        }
    }

    static function DBCredencial(string $SubDomain = '')
    {
        $Tenant = self::DetailsBySubDomain($SubDomain) ?: [];
        return !empty($Tenant) ? [
            'DB_Type' => $Tenant['DB_Type'] ?: '',
            'DB_Host' => $Tenant['DB_Host'] ?: '',
            'DB_Name' => $Tenant['DB_Name'] ?: '',
            'DB_User' => $Tenant['DB_User'] ?: '',
            'DB_Password' => $Tenant['DB_Password'] ?: '',
        ] :
            [
                'DB_Type' => '',
                'DB_Host' => '',
                'DB_Name' => '',
                'DB_User' => '',
                'DB_Password' => '',
            ];
    }
}

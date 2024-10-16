<?php

namespace System;

use System\Preload\Precocious;
use Symfony\Component\Dotenv\Dotenv;
use System\App\Tenant;
use System\Config\DB;
use System\Config\TenantValidate;
use System\Middleware\SystemValidate;
use System\Preload\SystemExc;

class Init
{

    function __construct()
    {
        error_reporting(E_ALL);
ini_set('display_errors', 1);

        define('AppStartTime', microtime(true));

        define('DS', '/');
        define('Root', str_replace('\\', '/', dirname(__FILE__) . DS) . '..' . DS);
        define('System', Root . 'System' . DS);
        define('Config', System . 'Config' . DS);
        define('Middleware', System . 'Middleware' . DS);
        define('Globals', System . 'Globals' . DS);
        define('Library', System . 'Library' . DS);
        define('Preload', System . 'Preload' . DS);
        define('Includes', System . 'Includes' . DS);
        define('App', Root . 'App' . DS);
        define('TenantBaseDir', Root . '..' . DS . 'Tenant' . DS);
        define('OwnerBaseDir', Root . '..' . DS . 'Owner' . DS);
        set_include_path($IncludePath = (Includes . PATH_SEPARATOR . get_include_path())); //  Why ??????????




        if (file_exists(Root . '.env')) {
            $dotenv = new Dotenv();
            $dotenv->load($df= Root . ($_SERVER['SERVER_ADDR'] == '127.0.0.1' ? '/.env.local': '/.env'));
            /* this is a easy way to load file without DotEnv class if you want to not using please use this code  */
            //$_ENV = parse_ini_file(Root . '.env');
            /* */
        } else
            throw new SystemExc(".env file not found. please make sure file is correct path");

        if (file_exists(OwnerBaseDir . '/.env')) {
            $dotenv->load(OwnerBaseDir . '/.env');
        } else {
        }
        foreach (array_merge(glob(Globals . '*.php'), glob(Preload . '*.php'),
         glob(Middleware . '*.php'), 
         glob(Config . '*.php'),
         glob(System . 'App' . DS . 'Trait' . DS . '*.php'), 
         glob(System . 'App' . DS . 'Session' . DS . '*.php'), glob(System . 'App' . DS . 'Tenant' . DS . '*.php'), glob(System . 'App' . DS . '*.php'), ) as $F)
            require $F;


        $OldTraceID = isset($_COOKIE['TraceID']) ? $_COOKIE['TraceID'] : '0000000000000000';
        define('OldTraceID', $OldTraceID); // Tracking ID Trace ID every hits (click)
        define('TraceID', GenerateUniqueID(16)); // Tracking ID Trace ID every hits (click)
        setcookie('TraceID', TraceID);
        $_SERVER['Tenant']['ID'] = 'Unauthorized';
        $_SERVER['Tenant']['Name'] = 'Unauthorized';
        /*         * /
          SELECT
          CASE WHEN condition1 THEN TrackID AS NewTrackID ELSE TrackID AS OldTrackID END,
          FROM your_table_name;
          /* */

        spl_autoload_register(function ($ClassName) {
            if (!empty($ClassName)) {
                if (file_exists(Library . $ClassName . '.php'))
                    require(Library . $ClassName . '.php');

                if (file_exists(App . 'Model' . DS . $ClassName . '.php'))
                    require(App . 'Model' . DS . $ClassName . '.php');
            }
        });


        if (self::SystemValidate()) {
            if (self::TenantValidate()) {
                (new Precocious())->Run();
            } else {
                throw new SystemExc('Tenant setup are not complated please do all of need system instllation..');
            }
        } else {
            throw new SystemExc('system setup are not complated please do all of need system instllation..');
        }

    }

    function SystemValidate()
    {
        try {
            new SystemValidate();
            return true;
        } catch (SystemExc $e) {
            throw new SystemExc($e->getMessage());
        }

    }
    function TenantValidate()
    {
        try {
            if (Tenant::IsTenant() == true) {
                new TenantValidate();
                return true;
            } else
                return true;
        } catch (SystemExc $e) {
            throw new SystemExc($e->getMessage());
        }

    }


}

new Init();

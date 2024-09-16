<?php

namespace System;

use Preload\Precocious;
use Symfony\Component\Dotenv\Dotenv;
use System\App\Tenant;

class Init {

    function __construct() {

        define('AppStartTime', microtime(true));

        define('DS', '/');
        define('Root', str_replace('\\', '/', dirname(__FILE__) . DS) . '..' . DS);
        define('System', Root . 'System' . DS);
        define('Config', System . 'Config' . DS);
        define('Globals', System . 'Globals' . DS);
        define('Library', System . 'Library' . DS);
        define('Preload', System . 'Preload' . DS);
        define('Includes', System . 'Includes' . DS);
        define('App', Root . 'App' . DS);
        define('TenantBaseDir', Root . '..' . DS . 'Tenant' . DS);
        set_include_path($IncludePath = (Includes . PATH_SEPARATOR . get_include_path())); //  Why ??????????




        $dotenv = new Dotenv();
        $dotenv->load(Root . '/.env');

        foreach (array_merge(
                glob(Globals . '*.php'),
                glob(Preload . '*.php'),
                glob(Config . '*.php'),
                glob(System . 'App' . DS . 'Trait' . DS . '*.php'),
                glob(System . 'App' . DS . 'Session' . DS . '*.php'),
                glob(System . 'App' . DS . 'Tenant' . DS . '*.php'),
                glob(System . 'App' . DS . '*.php'),
        ) As $F)
            require ($F);


        $OldTraceID = isset($_COOKIE['TraceID']) ? $_COOKIE['TraceID'] : '0000000000000000';
        define('OldTraceID', $OldTraceID); // Tracking ID Trace ID every hits (click)
        define('TraceID', GenerateUniqueID(16)); // Tracking ID Trace ID every hits (click)
        setcookie('TraceID', TraceID);
        /*         * /
          SELECT
          CASE WHEN condition1 THEN TrackID AS NewTrackID ELSE TrackID AS OldTrackID END,
          FROM your_table_name;
          /* */

        spl_autoload_register(function ($ClassName) {
            if (!empty($ClassName)) {
                if (file_exists(Library . $ClassName . '.php'))
                    require (Library . $ClassName . '.php');

                if (file_exists(App . 'Model' . DS . $ClassName . '.php'))
                    require (App . 'Model' . DS . $ClassName . '.php');
            }
        });

        //dd(Precocious::Install(0));
        (new Precocious())->Run();
    }
}

new Init();

<?php

namespace System\Preload;

use SystemExc;
use System\App\Tenant;
use System\App\Session;

class Precocious {

    public function __construct($URL = '') {
        (new Session())->Init();
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        sdd(' call From-' . FuncCallFrom());
        sdd('Domain=' . Domain());
        sdd('SubDomain=' . SubDomain());
        sdd('BaseDomain=' . BaseDomain());

        is_dir(TenantBaseDir) == true ?: throw new SystemExc('Tenant Folder is Required for this system. ' . TenantBaseDir, 11);

        $this->DomainConfig();
        $U = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'], '/')) : [DS];

        SaveAccessLogFile('Browse');

        define('Controller', (trim(trim($U[0]), DS)) ?: 'Home');
        define('Method', (isset($U[1]) && $U[1] ? $U[1] : 'Index'));
    }

    static function Install($ErrorCode = null) { // pending all code intall setup one time
        $R = [];
        if ($ErrorCode == 11 || $ErrorCode === 0)
            $R['CreateTenantBaseDir'] = is_dir(TenantBaseDir) ?: mkdir(TenantBaseDir, 0777);
            Tenant::Install(0, 0);
        foreach (Tenant::All() As $Tenant)
            Tenant::Install($Tenant['ID'], 0);
        return$R;
    }

    public function __destruct() {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        sdd(' call From-' . FuncCallFrom());
    }

    public function Run() {

        try {
            ini_set('display_errors', 1);

            

            1 == 1 ? $this->DynamicRouteSystem() : $this->DefaultRouteSystem();
        } catch (SystemExc $E) {
            $E->Response($E);
        } finally {
            sdd('Finaly Run in DMC->Run');
        }


        sdd('Controller - ' . Controller);
        sdd('Method - ' . Method);
    }

    public function DomainConfig() {
        if (Tenant::Permission() === true) {
            if (!empty(SubDomain()) && Tenant::IsVerified()) {
                
            }
        } elseif (Tenant::Permission() === false && !empty(SubDomain())) {
            //exit('Multi-tenant system are unavailable for this Domain ');
            throw new SystemExc('Multi-tenant system are unavailable for this Domain ', 404);
        } elseif (Tenant::Permission() === false && empty(SubDomain())) {
            $_SERVER['Tenant']['ID'] = 0;
            $_SERVER['Tenant']['Name'] = 0;
            define(TenantID, 0);
        } else {
        }
    }

    function DefaultRouteSystem() {
        try { /*         */ // Old MVC Pattern use URL/Controller/Function/
            Controller != 'Controller' && !empty(Controller) ?: throw new SystemExc('System Error - Global Controller not Defined', 1500);
            !file_exists(($FilePath = App . 'Controller' . DS . (Controller) . '.php')) ?
                            throw new SystemExc((Controller . ' Controller not Found @ ' . $FilePath), 1404) : null;
            require (App . 'Controller' . DS . (Controller) . '.php');
            !file_exists((class_exists($ClassName = Controller . 'Controller'))) ?: throw new SystemExc(($ClassName . ' Class not Exists'), 1404);
            $O = new $ClassName();

            if (Method !== 'Method' && !empty(Method)) {
                method_exists($O, Method) ?: throw new SystemExc((Method . ' Method is not Exists in ' . $ClassName), 1404);
                call_user_func(array($O, Method));
            }
        } catch (SystemExc $E) {
            $E->Response($E);
        }
    }

    function DynamicRouteSystem() {
        try {
            require App . 'Route.php'; // New Route method
            //System\App\Route::Dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
            \System\App\Route::Dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        } catch (SystemExc $E) {
            $E->Response($E);
        }
    }
}

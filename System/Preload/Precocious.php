<?php
namespace DhruvJoshi\Precocious;
class Precocious {

    public function __construct($URL = '') {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
        $U = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'], '/')) : [DS];
        define('Controller', (trim(trim($U[0]), DS)) ?: 'Home' );
        define('Method', (isset($U[1]) && $U[1] ? $U[1] : 'Index'));
    }

    public function __destruct() {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
    }

    public function Run() {

        try {
            Controller != 'Controller' && !empty(Controller) ?: throw new Exc('System Error - Global Controller not Defined', 1500);
            !file_exists(($FilePath = App . 'Controllers' . DS . (Controller) . '.php')) ? throw new Exc((Controller . ' Controller not Found @ ' . $FilePath), 1404) : null;
            require (App . 'Controllers' . DS . (Controller) . '.php');
            !file_exists((class_exists($ClassName = Controller . 'Controller'))) ?: throw new Exc(($ClassName . ' Class not Exists'), 1404);
            $O = new $ClassName();

            if (Method !== 'Method' && !empty(Method)) {
                method_exists($O, Method) ?: throw new Exc((Method . ' Method is not Exists in ' . $ClassName), 1404);
                call_user_func(array($O, Method));
            }
        } catch (Exception $E) {
            Response($E->getCode(), $E->getMessage(), isset($D) ? $D : [], $E);
        } finally {
            dd('Finaly Run in DMC->Run');
        }

        
        dd(Controller);
        dd(Method);
    }

}

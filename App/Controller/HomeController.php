<?php

//namespace App\Controller;

class HomeController extends \System\App\Controller {

    public function __construct() {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        sdd(' call From-' . FuncCallFrom());
    }

    public function Index($param=[]) {
        View('About');
    }

    public function Users($param = []) {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        sdd(' call From-' . FuncCallFrom());

        sdd(User::Get(), 1);
        sdd(Model\User()::Get());
        $M = new Model\User();
//        $M = new User();
        sdd($M->Get());
        sdd('+++++++++++++++++++++++++');
        //$M = new User(); sdd($M->Get());
    }

    public function __destruct() {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        sdd(' call From-' . FuncCallFrom());
    }

}

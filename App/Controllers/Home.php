<?php

class HomeController extends Controller {

    public function __construct() {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
    }

    public function Index($param=[]) {
        View('Response');
    }

    public function Users($param = []) {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());

        dd(User::Get(), 1);
        dd(Model\User()::Get());
        $M = new Model\User();
//        $M = new User();
        dd($M->Get());
        dd('+++++++++++++++++++++++++');
        //$M = new User(); dd($M->Get());
    }

    public function __destruct() {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
    }

}

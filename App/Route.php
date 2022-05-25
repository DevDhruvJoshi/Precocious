<?php
namespace System;
final class Route {
//Ref @ https://dev.to/fadymr/php-create-your-own-php-router-4g0o
    public function __construct($URL = '') {
        $U = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'], '/')) : '/';
        define('Controller', $U[0]);
        define('Method', $U[1]);
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
    }

    public function __destruct() {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
    }

    public function Run() {
        dd(Controller);
        dd(Method);
    }

}

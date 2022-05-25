<?php

class Exc extends Exception {

    function __construct($M, $C) {
        parent::__construct($M, $C);
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        //echo $this->getTraceAsString();
        //dd(debug_backtrace());// This is best but pending pretty
        //dd(GetCallingMethodName());//UDF
    }

    
    function __destruct() {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
    }

}

<?php

class Controller extends DMVC {

    public function __construct() {
        dd('call ' . __CLASS__ . ' Fun ' . __FUNCTION__);
    }

    public function __destruct() {
        dd('call ' . __CLASS__ . ' Fun ' . __FUNCTION__);
    }

}

<?php
use DhruvJoshi\Precocious;
class Controller extends Precocious{

    public function __construct() {
        dd('call ' . __CLASS__ . ' Fun ' . __FUNCTION__);
    }

    public function __destruct() {
        dd('call ' . __CLASS__ . ' Fun ' . __FUNCTION__);
    }

}

<?php

namespace System\App;

use System\Preload\Precocious;
class Controller extends Precocious{

    public function __construct() {
        sdd('call ' . __CLASS__ . ' Fun ' . __FUNCTION__);
    }

    public function __destruct() {
        sdd('call ' . __CLASS__ . ' Fun ' . __FUNCTION__);
    }

}

<?php

/**
 * The about page controller
 */
class AboutController extends Controller {

    private $modelObj;

    function __construct($model) {
        dd('call ' . __CLASS__ . ' Fun ' . __FUNCTION__);
        $this->modelObj = $model;
    }

    public function current() {
        dd('call ' . __CLASS__ . ' Fun ' . __FUNCTION__);
        return $this->modelObj->message = "About us today changed by aboutController.";
    }

}

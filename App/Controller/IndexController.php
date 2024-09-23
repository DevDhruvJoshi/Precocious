<?php

namespace App\Controller;

use System\App\Controller;

/**
 * The home page controller
 */
class IndexController extends Controller {

    private $model;

    function __construct($model) {
        $this->model = $model;
    }

    public function sayWelcome() {
        return $this->model->welcomeMessage();
    }

}

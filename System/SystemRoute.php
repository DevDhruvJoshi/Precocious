<?php

use System\App\Route;
use System\Preload\Precocious;


Route::Get('/', function () {
    echo "This is the System route home";
});

Route::Get('/install', function () {
    Precocious::Install();
});

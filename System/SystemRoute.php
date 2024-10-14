<?php

use System\App\Route;
use System\App\Tenant;
use System\Preload\Precocious;


Route::Get('/', function () {
    echo "This is the System route home";
});

Route::Get('/addtenant', function () {
    Tenant::AddNew('User' . rand(1, 9));
});

Route::Get('/install', function () {
    Precocious::Install();
});

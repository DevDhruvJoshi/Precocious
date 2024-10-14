<?php

use System\App\Route;
use App\Model\Contact;
use System\App\Tenant;

Route::Get('/', function () {
    dd(Tenant::Data());
    echo "This is the Tenant route home";
    dd(Contact::All([], ['ID' => 'desc'], false));
});
<?php

use System\App\Route;
use App\Model\Contact;


Route::Get('/', function () {
    echo "This is the Tenant route home";
    dd(Contact::All([], ['ID' => 'desc'], false));
});
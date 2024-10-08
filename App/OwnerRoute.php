<?php

use App\Model\Contact;
use System\App\Route;

Route::Get('/', function () {
    echo "This is the Owner route home";
    dd(Contact::All([], ['ID' => 'desc'], false));
});
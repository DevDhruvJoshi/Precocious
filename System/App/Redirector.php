<?php

use System\App\Route;

class Redirector
{
    public function route(string $name, array $params = []): void
    {
        $url = Route::Route($name);
        header("Location: $url", true, 302);
        exit;
    }
}
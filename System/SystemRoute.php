<?php

use App\Controller\ContactController;
use App\Controller\UserController;
use App\Middleware\Middleware;
use System\App\Route;
use System\App\Tenant;
use System\Preload\Precocious;
use System\Preload\SystemExc;

Route::Middleware([Middleware::class, 'logRouteAccess']);

Route::Middleware([new Middleware(), 'logRouteAccess']); // Call specific method of Middleware

Route::Get('/', function () {
    return "Welcome to the homepage!" . BaseDomain();
});
/* */

Route::Get('/clearroutescache', function () {
    Route::ClearRoutesCache();
    dd("ClearRoutesCache");
});


Route::Prefix('doc', function () {
    Route::Get('/db', function () {
        return SystemView('Documentation/DatabaseCredential');
    });
    Route::Get('/route', function () {
        return SystemView('Documentation/Route');
    });
});


/*  need to update in this base route class it's not a working proper */
Route::Domain('{account}.' . BaseDomain(), function () {
    Route::Get('/sub/{id}', function (string $account, string $id) {
        // Ensure this closure is invoked correctly
        dd("Account: $account, User ID: $id");
    });
});


Route::Get('/phpinfo', function () {
    phpinfo();
});

Route::Redirect('/there', '/exit', 301);
Route::PermanentRedirect('/exit', '/closed');
Route::View('/welcome', 'welcome', ['name' => 'Taylor']);
// Dynamic route with improved handling
Route::Get('/test/{ID}/{Name}', function ($ID, $Name) {
    var_dump(['ID' => $ID, 'Name' => $Name]);
    var_dump($_REQUEST);
});



Route::Resource('articles', 'ArticleController');

Route::Prefix('admin', function () {
    Route::Get('dashboard', function () {
        // Admin dashboard logic
    });

    Route::Group(['prefix' => 'users'], function () {
        Route::Get('/', function () {
            // List all users
        });

        Route::Get('{id}', function ($id) {
            // Get user by ID
        }, null, ['id' => '/\d+/']); // Constraint for id to be a digit
    });
});



Route::AddRoute('GET', 'posts/{slug}', function ($slug) {
    // Fetch post by slug
}, null, ['slug' => '/^[a-zA-Z0-9-]+$/']); // Constraint for slug to be alphanumeric and dashes
Route::Middleware(function ($routePath) {
    // Check user authentication
    if ((1 == 2)) {
        throw new SystemExc("Unauthorized access to route '$routePath'.");
    }
});



// Usage of middleware in a group
Route::Group(['prefix' => 'secure'], function () {
    Route::Get('dashboard', function () {
        // Secure dashboard logic
    });
});





// Grouping user-related routes with dynamic capabilities
Route::Group(['prefix' => 'group'], function () {
    Route::Get('users', [ContactController::class, 'index']);
    Route::Get('users/{id}', [UserController::class, 'show']);
    Route::Post('users', [UserController::class, 'create']);
    Route::Put('users/{id}', [UserController::class, 'update']);
    Route::Delete('users/{id}', [UserController::class, 'delete']);

    // Nested resources example
    Route::Get('users/{userId}/posts', function ($userId) {
        dd('dynamic routes is done ' . $userId);
    });
    Route::Get('users/{userId}/posts/{postId}', [UserController::class, 'getPost']);
});




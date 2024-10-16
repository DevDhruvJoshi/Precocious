<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Class Documentation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1, h2 {
            color: #333;
        }
        pre {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 10px;
            overflow: auto;
            border-radius: 5px;
        }
        code {
            color: #c7254e;
            background-color: #f9f2f4;
            padding: 2px 4px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<h1>Route Class Documentation</h1>

<p>This document provides an overview of the <code>Route</code> class, showcasing its methods and usage examples.</p>

<h2>Route Class Code</h2>
<pre>
<?php
// Route class code goes here
?>
</pre>

<h2>Usage Examples</h2>

<h3>1. Basic Route Registration</h3>
<pre>
Route::Get('/home', function () {
    return "Welcome to the Home Page!";
});
</pre>

<h3>2. Route with Parameters</h3>
<pre>
Route::Get('/user/{id}', function ($Id) {
    return "User ID: $Id";
});
</pre>

<h3>3. Grouping Routes with Prefix</h3>
<pre>
Route::Prefix('admin', function () {
    Route::Get('/dashboard', function () {
        return "Admin Dashboard";
    });

    Route::Get('/settings', function () {
        return "Admin Settings";
    });
});
</pre>

<h3>4. Middleware Registration</h3>
<pre>
Route::Middleware(function ($RoutePath) {
    if (!isLoggedIn()) {
        throw new SystemExc("Unauthorized access!");
    }
});
</pre>

<h3>5. Using Middleware with Routes</h3>
<pre>
Route::Get('/profile', function () {
    return "User Profile";
})->Middleware([UserAuthMiddleware::class, 'check']);
</pre>

<h3>6. Redirecting Routes</h3>
<pre>
Route::Redirect('/old-path', '/new-path');
</pre>

<h3>7. Permanent Redirect</h3>
<pre>
Route::PermanentRedirect('/old-home', '/new-home');
</pre>

<h3>8. View Rendering</h3>
<pre>
Route::View('/about', 'aboutView', ['title' => 'About Us']);
</pre>

<h3>9. Handling Different HTTP Methods</h3>
<pre>
Route::Post('/submit', function () {
    return "Form Submitted!";
});

Route::Put('/update', function () {
    return "Data Updated!";
});

Route::Delete('/remove', function () {
    return "Data Removed!";
});
</pre>

<h3>10. Catch-All Route</h3>
<pre>
Route::Any('/fallback', function () {
    return "No route matched!";
});
</pre>

<h3>11. Domain Routing</h3>
<pre>
Route::Domain('{account}.example.com', function () {
    Route::Get('/', function () {
        return "Welcome to your account!";
    });
});
</pre>

<h3>12. Loading Routes from Cache</h3>
<pre>
Route::LoadRoutesFromCache();
</pre>

<h3>13. Saving Routes to Cache</h3>
<pre>
Route::SaveRoutesToCache();
</pre>

<h3>14. Clearing Routes Cache</h3>
<pre>
Route::ClearRoutesCache();
</pre>

<h3>15. Handling Different Route Scenarios</h3>
<pre>
Route::Any('/contact', function () {
    return "Contact Page";
});
</pre>

<h3>16. Combining Routes with Parameters and Middleware</h3>
<pre>
Route::Middleware([AuthMiddleware::class, 'check']);

Route::Get('/dashboard/{userId}', function ($UserId) {
    return "Dashboard for User ID: $UserId";
});
</pre>

</body>
</html>

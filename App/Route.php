<?php

use System\App\Route;

Route::Get('/', function () {
    echo "This is the home route";
    dd(App\Model\Contact::All([], ['ID' => 'desc'], false));
});

Route::Get('/phpinfo', function () {
    phpinfo();
});
Route::Get('/dbunittest', function () {
    $db = new \System\Config\DB();
/* * /
    dd('start - user insert');
    $userId = $db->Insert('users', [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com'
    ]);
    dd('end - user insert ID = ' . $userId);

    dd('start - post insert ');
    $postid = $db->Insert('posts', [
        'user_id' => $userId,
        'title' => 'My First Post',
        'content' => 'This is the content of my first post.'
    ]);
    dd('End - post insert id=' . $postid);

    dd('start - Select posts');
    $results = $db->Select('posts', ['*'], ['user_id' => $userId]);
    foreach ($results as $row) {
        echo $row['title'] . '<br>';
    }
    dd('end - Select posts');

    dd('start - update user');
    $updateuser = $db->Update('users', ['name' => 'Jane Doe'], ['id' => $userId]);
    dd('end - update user = ' . $updateuser);

    dd('start - Delete post');
    $deletepost = $db->Delete('posts', ['id' => $postid]);
    dd('end - Delete post = ' . $deletepost);

    dd('start - Delete user');
    $deleteuser = $db->Delete('users', ['id' => $userId]);
    dd('end - Delete user = ' . $deleteuser);

    dd('select User by query() $result');
    $result = $db->Fetch($db->Query('SELECT * FROM users'));
    dd($result);
    dd('select User by query() id =5 $result');
    $result = $db->Fetch($db->Query('SELECT * FROM users WHERE id = ? or username=?', [5, 'test']));
    dd($result);
/* */
     //return SystemView('Exception/DB',[ ],$Content = false);
    $result = $db->Fetch($db->Query('SELECT * FROM userss WHERE id = ? or username=?', [5, 'test']));
});

Route::Get('/about', function () {
    echo "This is the about Route";

    dd(App\Model\Contact::Update(['Name' => rand(111111111, 999999999999)], 13, []));
    dd(App\Model\Contact::All([], ['ID' => 'desc'], false));
    dd('All Tenats');
    dd(\System\App\Tenant::All());
});

Route::Get('/dashboard', function () {
    return View('Dashboard');
});
Route::Get('/logout', [App\Controller\UserController::class, 'Logout']);

Route::Get('/register', function () {
    return View('Register');
});

Route::Post('/registernow', [App\Controller\UserController::class, 'Register']);

Route::Get('/login', function () {
    return View('Login');
});
Route::Post('/loginnow', [App\Controller\UserController::class, 'Login']);

Route::Get('/about/{ID}', [App\Controller\AboutController::class, 'index']);

Route::Get('/cotactbyid/{ID}', function ($ID) {
    dd(App\Model\Contact::ID($ID, '1 desc', false));
});
Route::Any('/directview', function () {
    return View('Contact');
});

Route::Post('/users', function () {
    // Simulate retrieving user list from a database
    $users = [
        ['id' => 1, 'name' => 'John Doe'],
        ['id' => 2, 'name' => 'Jane Smith'],
    ];
    return json_encode($users);
});

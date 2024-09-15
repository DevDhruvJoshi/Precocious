<?php

namespace App\Controller;

use App\Model\Contact As Contact;

class UserController extends \System\App\Controller {

    function index($ID) {
        return View('Users');
    }

    public function Register() {
        try {



            $UserID = \App\Model\User::Save([
                        'email' => $_POST['email'],
                        'username' => $_POST['username'],
                        'password' => md5($_POST['password']),
            ]);
            if ($UserID > 0) {
                echo 'now login <a href="/login">click here</a>';
            } else
                throw new Exception('not inserted ');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public function Login() {
        try {



//            $User = \App\Model\User::SelectOnes([], 'username = "' . $_POST['username'] . '"');
            $User = \App\Model\User::SelectOnes(['*'], ['username' => $_POST['username'] ]);
            if ($User) {
                if (md5($_POST['password']) === $User['password']) {
                    \System\App\Session::Start();

                    $_SESSION['User']['ID'] = $User['id'];
                    $_SESSION['User']['Name'] = $User['username'];
                    header('Location: /dashboard');
                } else
                    throw new \Exception('Invalid password');
            } else
                throw new \Exception('User Not found');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public function Logout() {
        try {
                if (isset($_SESSION['User']['ID'])) {

 
                    session_unset();
                    session_destroy();

                    header('Location: /login');
                } else
                    throw new \Exception('you are already logout');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
}

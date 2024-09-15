<?php

namespace System\App;

class Session {

    public function __construct() {
        $this->SetCookieConfig();
        /* */ // Set other type of session handler 
        if (env('SessionHandler') == 'Database' || env('SessionHandler') == 'DB')
            session_set_save_handler(new \System\App\Session\DBBased(), true);
        else if (env('SessionHandler') == 'memchached') { // very fast but non secure 
            // need other type 
        } else if (env('SessionHandler') == 'redis') { // fast and secure
            // need other type 
        } else { // File 
            // No need to code php default set everything
        }
        /* */
        session_start();
    }

    function Init() {
        return true;
    }

    function SetCookieConfig() {
        session_set_cookie_params([
            'lifetime' => 300, // Second
            'path' => '/',
            'domain' => Domain(),
            'secure' => isset($_SERVER['HTTPS']) ? true : false,
            'httponly' => true, // Set to true to prevent JavaScript access to the session cookie. Cross-Site Scripting
            'samesite' => 'Strict' // Strict || Lax // (Cross-Site Request Forgery
        ]);
    }

    public static function Start() {
        Session::Distroy();
        session_id(self::GenerateNewID());
        session_start();
        session_regenerate_id(true);
        if (rand(1, 1000) == 1) {
            (gc_collect_cycles()); // pending to check 
        }
        return session_id();
    }

    public static function Distroy() {
        setcookie('PHPSESSID', '', time() - 3600); // it's can remove old PHPSESSID from browser
        session_unset(); // Unset specific session variables
        session_destroy(); // Destroy the session
    }

    private static function GenerateNewID() {
        return bin2hex(random_bytes(32));
    }
}

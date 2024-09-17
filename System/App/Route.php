<?php

namespace System\App;

use System\Preload\Exc;
use System\Preload\SystemExc;

final class Route {

    private static $Routes = [];

    public static function Get($Path, $CallBack) {
        self::$Routes[$Path]['method'] = 'GET';
        self::$Routes[$Path]['callback'] = $CallBack;
        self::$Routes[$Path]['pattern'] = '/^' . str_replace(['/', '{', '}'], ['\/', '(?P<', '>'], $Path) . '$/'; // Escape slashes, define capture groups
    }

    public static function Post($Path, $CallBack) {
        self::$Routes[$Path]['method'] = 'POST';
        self::$Routes[$Path]['callback'] = $CallBack;
    }

    public static function Any($Path, $CallBack) {
        self::$Routes[$Path]['method'] = 'ANY';
        self::$Routes[$Path]['callback'] = $CallBack;
    }

    // Additional methods for PUT, PATCH, DELETE, OPTIONS, etc. as needed

    public static function Dispatch($uri, $method = 'GET') {
        $uri = strtolower($uri);

        if (array_key_exists($uri, self::$Routes)) {
            if ((self::$Routes[$uri]['method'] === 'ANY') || (self::$Routes[$uri]['method'] === $method)) {
                $CallBack = self::$Routes[$uri]['callback']; // Store the callback
                // Handle controller resolution and method call (assuming a closure)
                if (is_callable($CallBack)) {
                    return call_user_func($CallBack); // Call the callback directly if it's callable
                } else {
                    $controller = self::resolveController($CallBack[0]); // Assuming first element is controller class
                    return call_user_func_array([$controller, $CallBack[1]], []); // Call controller method with empty arguments array
                    if ($controller) {
                        
                    } else {
                        throw new SystemExc("Controller class '" . $CallBack[0] . "' not found");
                    }
                }
            } else {
                throw new SystemExc("Method '$method' not allowed for '$uri'");
            }
        } else {
            throw new SystemExc("Route '$uri' not found");
        }
    }

    
    private static function resolveController($controllerClass) {
        $namespace = ''; // Replace with your namespace
        if (strpos($controllerClass, '\\') === false) {
            $controllerClass = $namespace . $controllerClass;
        }

        if (class_exists($controllerClass)) {
            return new $controllerClass;
        } else {
            return null;
        }
    }
}
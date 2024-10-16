<?php
namespace App\Middleware;

class Middleware
{
    private static array $middleware = [];

    // Method to register middleware
    public static function register(callable $middleware): void
    {
        self::$middleware[] = $middleware;
    }

    // Method to execute all registered middleware
    public static function execute(string $routePath): void
    {
        foreach (self::$middleware as $middleware) {
            call_user_func($middleware, $routePath);
        }
    }
    static function  logRouteAccess(...$args)
    {
        //dd('function call '.__FUNCTION__);
        //dd($args);
        //error_log("Accessed route: $routePath");
    }
}

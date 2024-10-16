<?php
namespace System\App;

use System\Preload\SystemExc;

final class Route
{
    private static array $Routes = [];
    private static array $MethodRoutes = [];
    private static string $CurrentPrefix = '';
    private static array $Middleware = [];
    private static array $NamedRoutes = [];
    private static array $RouteConstraints = [];
    private static string $Namespace = ''; // Define your namespace here

    public static function Prefix(string $Prefix, callable $Callback): void
    {
        if (empty($Prefix) || !is_callable($Callback)) {
            throw new SystemExc("Invalid arguments for Prefix: prefix must be a non-empty string and callback must be callable.");
        }

        $Attributes = ['prefix' => $Prefix];
        self::Group($Attributes, $Callback);
    }

    public static function Group(array $Attributes, callable $Callback): void
    {
        $Prefix = $Attributes['prefix'] ?? '';
        $OriginalPrefix = self::$CurrentPrefix;

        if (empty($Prefix)) {
            throw new SystemExc("Group prefix cannot be empty.");
        }

        self::$CurrentPrefix = rtrim($OriginalPrefix . '/' . $Prefix, '/');
        $Callback();
        self::$CurrentPrefix = $OriginalPrefix;
    }

    private static function CombinePath(string $Path): string
    {
        return self::$CurrentPrefix . '/' . ltrim($Path, '/');
    }

    static function AddRoute(string $Method, string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        if (empty($Method) || empty($Path)) {
            throw new SystemExc("Method and path must not be empty.");
        }

        if (!is_callable($Callback) && !(is_array($Callback) && count($Callback) === 2)) {
            throw new SystemExc("Callback must be callable or an array with controller and method.");
        }

        $FullPath = self::CombinePath($Path);
        self::$Routes[$FullPath] = [
            'method' => $Method,
            'callback' => $Callback,
            'pattern' => self::GeneratePattern($FullPath),
            'constraints' => $Constraints,
        ];
        self::$MethodRoutes[$Method][$FullPath] = self::$Routes[$FullPath];

        if ($Name) {
            self::$NamedRoutes[$Name] = $FullPath; // Store named route
        }
    }

    private static function GeneratePattern(string $Path): string
    {
        return '#^' . preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $Path) . '$#';
    }

    public static function Dispatch(string $Uri, string $Method = 'GET')
    {
        if (empty($Uri) || empty($Method)) {
            throw new SystemExc("Invalid arguments for Dispatch: URI and method must not be empty.");
        }

        $ParsedUrl = parse_url($Uri);
        if (!isset($ParsedUrl['path'])) {
            throw new SystemExc("Invalid URI: '$Uri'. Could not parse path.");
        }

        $Path = strtolower($ParsedUrl['path']);

        if (isset(self::$MethodRoutes[$Method])) {
            foreach (self::$MethodRoutes[$Method] as $RoutePath => $Route) {
                if (preg_match($Route['pattern'], $Path, $Matches)) {
                    $Params = array_filter($Matches, 'is_string', ARRAY_FILTER_USE_KEY);

                    // Validate parameters against constraints
                    if (!self::ValidateParams($Params, $Route['constraints'])) {
                        throw new SystemExc("Route parameters do not satisfy constraints for route '$RoutePath'.");
                    }

                    // Run middleware before executing the route
                    self::RunMiddleware($RoutePath);
                    return self::HandleCallback($Route['callback'], array_values($Params));
                }
            }
        }

        throw new SystemExc("Route '$Path' not found or method '$Method' not allowed.");
    }

    private static function ValidateParams(array $Params, array $Constraints): bool
    {
        foreach ($Constraints as $param => $constraint) {
            if (isset($Params[$param]) && !preg_match($constraint, $Params[$param])) {
                return false;
            }
        }
        return true;
    }

    private static function RunMiddleware(string $RoutePath): void
    {
        foreach (self::$Middleware as $Middleware) {
            if (is_callable($Middleware)) {
                call_user_func($Middleware, $RoutePath);
            } else {
                throw new SystemExc("Middleware for route '$RoutePath' is not callable.");
            }
        }
    }

    private static function HandleCallback($Callback, array $Params)
    {
        if (is_callable($Callback)) {
            return call_user_func_array($Callback, $Params);
        } elseif (is_array($Callback) && count($Callback) === 2) {
            [$ControllerClass, $Method] = $Callback;
            $ControllerInstance = self::ResolveController($ControllerClass);
            if ($ControllerInstance && method_exists($ControllerInstance, $Method)) {
                return call_user_func_array([$ControllerInstance, $Method], $Params);
            }
        }

        throw new SystemExc("Invalid callback for route: " . json_encode($Callback));
    }

    public static function View(string $Path, string $View, array $Data = []): void
    {
        if (empty($Path) || empty($View)) {
            throw new SystemExc("Invalid arguments for View: Path and view must not be empty.");
        }

        self::AddRoute('GET', $Path, function () use ($View, $Data) {
            return View($View, $Data); // Assuming View() is a globally accessible function
        });
    }

    public static function Domain(string $DomainPattern, callable $Callback): void
    {
        if (empty($DomainPattern) || !is_callable($Callback)) {
            throw new SystemExc("Invalid arguments for Domain: Domain pattern must be non-empty and callback must be callable.");
        }

        if (preg_match('/\{(\w+)\}/', $DomainPattern, $Matches)) {
            $AccountVariable = $Matches[1];

            // Get the current account from the subdomain
            $Account = SubDomain() ?: ''; // Assuming this returns the subdomain/account

            // Store the original prefix
            $OriginalPrefix = self::$CurrentPrefix;

            // Set the current prefix to include the account
            self::$CurrentPrefix = str_replace('{' . $AccountVariable . '}', $Account, $OriginalPrefix);

            // Call the provided callback to register routes
            $Callback();

            // Reset the current prefix after the callback
            self::$CurrentPrefix = $OriginalPrefix;
        } else {
            throw new SystemExc("Invalid domain pattern '$DomainPattern'. Must include a variable in the form '{variable}'.");
        }
    }

    public static function Resource(string $Name, string $Controller): void
    {
        $actions = [
            'index' => 'GET',
            'show' => 'GET /{id}',
            'create' => 'GET /create',
            'store' => 'POST',
            'edit' => 'GET /{id}/edit',
            'update' => 'PUT /{id}',
            'destroy' => 'DELETE /{id}',
        ];

        foreach ($actions as $action => $methodPath) {
            [$method, $path] = array_pad(explode(' ', $methodPath, 2), 2, null);
            $path = "$Name/$path";
            self::AddRoute($method, $path, [$Controller, $action], null, $action === 'show' ? ['id' => '/\d+/'] : []);
        }
    }

    public static function PermanentRedirect(string $From, string $To): void
    {
        self::Redirect($From, $To, 301);
    }

    public static function Redirect(string $From, string $To, int $Status = 302): void
    {
        if (empty($From) || empty($To) || !in_array($Status, [301, 302])) {
            throw new SystemExc("Invalid arguments for Redirect: From and to must not be empty, and status must be 301 or 302.");
        }

        self::AddRoute('GET', $From, function () use ($To, $Status) {
            http_response_code($Status);
            header("Location: $To");
            exit; // Consider returning a response object instead of exit
        });
    }

    public static function Get(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('GET', $Path, $Callback, $Name, $Constraints);
    }

    public static function Post(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('POST', $Path, $Callback, $Name, $Constraints);
    }

    public static function Put(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('PUT', $Path, $Callback, $Name, $Constraints);
    }

    public static function Patch(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('PATCH', $Path, $Callback, $Name, $Constraints);
    }

    public static function Delete(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('DELETE', $Path, $Callback, $Name, $Constraints);
    }

    public static function Options(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('OPTIONS', $Path, $Callback, $Name, $Constraints);
    }

    public static function Head(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('ANY', $Path, $Callback, $Name, $Constraints);
    }

    public static function Connect(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('ANY', $Path, $Callback, $Name, $Constraints);
    }

    public static function Trace(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('ANY', $Path, $Callback, $Name, $Constraints);
    }

    public static function Any(string $Path, $Callback, string $Name = null, array $Constraints = []): void
    {
        self::AddRoute('ANY', $Path, $Callback, $Name, $Constraints);
    }

    private static function ResolveController(string $ControllerClass)
    {
        if (empty($ControllerClass)) {
            throw new SystemExc("Invalid controller class provided to ResolveController. It must not be empty.");
        }

        if (strpos($ControllerClass, '\\') === false) {
            $ControllerClass = self::$Namespace . $ControllerClass; // Use defined namespace
        }

        if (class_exists($ControllerClass)) {
            return new $ControllerClass;
        }

        throw new SystemExc("Controller class '$ControllerClass' does not exist.");
    }

    public static function Middleware($Middleware): void
    {
        if (is_array($Middleware) && count($Middleware) === 2) {
            list($Handler, $Method) = $Middleware;

            if (is_string($Handler) && class_exists($Handler)) {
                if (!is_callable([$Handler, $Method])) {
                    throw new SystemExc("Method '" . htmlspecialchars($Method) . "' in class '" . htmlspecialchars($Handler) . "' is not callable or does not exist.");
                }
            } elseif (is_object($Handler)) {
                if (!is_callable([$Handler, $Method])) {
                    throw new SystemExc("Method '" . htmlspecialchars($Method) . "' in instance of class '" . get_class($Handler) . "' is not callable.");
                }
            } else {
                throw new SystemExc("Middleware must be a callable function or an array with class and method.");
            }

            self::$Middleware[] = $Middleware;
            return;
        }

        if (is_callable($Middleware)) {
            self::$Middleware[] = $Middleware;
            return;
        }

        throw new SystemExc("Middleware must be a callable function or an array with class and method.");
    }

    public static function LoadRoutesFromCache(): void
    {
        if (!file_exists('routes.cache')) {
            // Optionally, log this event or handle it accordingly
            return; // Or throw a different exception if needed
        }

        try {
            $Data = file_get_contents('routes.cache');
            if ($Data === false) {
                throw new SystemExc("Failed to read the routes cache file.");
            }
            self::$Routes = unserialize($Data);
            if (self::$Routes === false && $Data !== 'b:0;') {
                throw new SystemExc("Failed to unserialize the routes cache. The data may be corrupted.");
            }
        } catch (\Exception $e) {
            throw new SystemExc("Error loading routes from cache: " . $e->getMessage());
        }
    }


    public static function ClearRoutesCache(): void
    {
        try {
            if (file_exists('routes.cache')) {
                if (!unlink('routes.cache')) {
                    throw new SystemExc("Failed to delete the routes cache file.");
                }
            } else {
                throw new SystemExc("Routes cache file does not exist to clear.");
            }
        } catch (\Exception $e) {
            throw new SystemExc("Error clearing routes cache: " . $e->getMessage());
        }
    }

    public static function SaveRoutesToCache(): void
    {
        try {
            $CacheableRoutes = [];

            foreach (self::$Routes as $RoutePath => $Route) {
                if (!is_callable($Route['callback']) || (is_array($Route['callback']) && count($Route['callback']) === 2)) {
                    $CacheableRoutes[$RoutePath] = $Route;
                }
            }

            $Result = file_put_contents('routes.cache', serialize($CacheableRoutes));
            if ($Result === false) {
                throw new SystemExc("Failed to write to the routes cache file.");
            }
        } catch (\Exception $e) {
            throw new SystemExc("Error saving routes to cache: " . $e->getMessage());
        }
    }
}

<?php

function vd($D, $E = 0) {
    var_dump($D);
    if ($E == 1)
        exit();
}

function sdd(...$args) { // it's only for framwork debuging
    //return dd(...$args);
}

function dd($V, $E = 0) {
    echo!is_array($V) ? '' : '<pre>';
    echo ('</br>');
    if (!is_array($V))
        echo $V ?: 'empty';
    else
        var_dump($V ?: 'empty');
    echo!is_array($V) ? '' : '</pre>';
    echo ('</br>');
    if ($E == 1)
        exit();
}

function env(string $K) {
    return isset($K) ? $_ENV[$K] : null;
}

function FuncCallFrom() {
    $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    return $caller = isset($dbt[2]['function']) ? (isset($dbt[2]['class']) ? trim(str_replace('\\', '/', ($dbt[2]['file'])), Root) . '@' . ($dbt[2]['class'] . $dbt[2]['type']) : '') . $dbt[2]['function'] : null;
}

//function Response($S, $M, $D, Exc|DBExc|Exception|SystemExc|null $E = null) {
function Response($S, $M, $D, $E = null) {
    /*     */
    View('Response', [
        'Status' => $S,
        'Msg' => $M,
        'Data' => $D,
        'SubView' => ( View('About', ['Status' => $S], true)),
        'Exc' => $E,
            ]
    );
    /* */
}

/**
 * <p>Render Template e.g. : <code>View('Auth/Login', ['Email'=>'mail@DhruvJoshi.Dev','Mobile'=>9999999999])</code>.</p>
 * @param string $FileName <p> View Path</p>
 * @param array $Data <p>any data you can view</p>
 * @param bool $ReturnContent <p>default is false</p>
 * @return HTML string|View
 * @Link https://DhruvJoshi.Dev/Functions/View()
 * @Since 27-10-2021
 * @DevelopBy Dev.Dhruv Joshi
 */
//function View(string $string, string $token): string|false {}
function View(string $File = '', array $D = [], bool $ReturnContent = false): string|false|null {
    echo!class_exists('View') ? include System . 'App' . DS . 'View.php' : '';
    if ($ReturnContent == true)
        return (new View($File, ($D ?: []), $ReturnContent, $IsForSytem = false) )->Content();
    else
        echo (new View($File, ($D ?: []), $ReturnContent, $IsForSytem = false))->Render();
    return '';
}

function SystemView(string $File = '', array $D = [], bool $ReturnContent = false): string|false|null {
    echo!class_exists('View') ? include System . 'App' . DS . 'View.php' : '';
    if ($ReturnContent == true)
        return (new View($File, ($D ?: []), $ReturnContent, $IsForSytem = true) )->Content();
    else
        echo (new View($File, ($D ?: []), $ReturnContent, $IsForSytem = true))->Render();
    return '';
}

function GetClassVariableValue($CN = null, $Var = null) {


    if (class_exists($CN)) {
        $staticVars = get_class_vars($CN);

        // Check if the dynamic variable exists in static properties
        if (isset($staticVars[$Var])) {
            return $staticVars[$Var];
        } else {
            // Handle non-existent property
        }
    } else {
        // Handle non-existent class
    }
}

function SubDomain() {
    $host = $_SERVER['HTTP_HOST'];
    $parts = explode('.', $host);
    return isset($parts[0]) && count($parts) === 3 ? strtolower($parts[0]) : null;
}

function BaseDomain() {
    $host = $_SERVER['HTTP_HOST'];
    $parts = explode('.', $host);
    return isset($parts[1]) ? strtolower(!empty(SubDomain()) ? ($parts[1] . '.' . $parts[2]) : ($parts[0] . '.' . $parts[1])) : null;
}

function Domain() {
    return $_SERVER['HTTP_HOST'];
}

function ClientIP($IPV6 = true) {
    if ($IPV6 == true)
        return filter_var(ClientIP(false), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ?: ClientIP(false);
    else {
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        elseif (isset($_SERVER['REMOTE_ADDR']))
            return $_SERVER['REMOTE_ADDR'];
    }
}

function Client() {
    $UserAgent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/(Chrome|Firefox|Safari|Opera|Edge|IE)\/([\d.]+)/', $UserAgent, $M)) {
        $Browser = $M[1];
        $BV = $M[2];
    } else {
        $Browser = 'NA';
        $BV = '';
    }
    if (preg_match('/Windows NT ([\d.]+)/', $UserAgent, $M)) {
        $Platform = 'Windows';
        $PlatformVersion = $M[1];
        $PlatformBit = preg_match('/(Win64|x64)/', $UserAgent) ? '64-bit' : '32-bit';
    } elseif (preg_match('/Mac OS X ([\d.]+)/', $UserAgent, $M)) {
        $Platform = 'macOS';
        $PlatformVersion = $M[1];
        $PlatformBit = preg_match('/Intel/', $UserAgent) ? '64-bit' : '32-bit';
    } elseif (strpos($UserAgent, 'Linux') !== false) {
        $Platform = 'Linux';
        $PlatformVersion = 'NA'; // Linux version is not always included in the user agent
        if (preg_match('/(x86_64|arm64|i686|aarch64|ppc64le|ppc64|x86|armv7l|armv7|armv6|armv5|arm|mips64|mips|sh4|sh2)/', $UserAgent, $M)) {
            $PlatformBit = $M[1];
        } else {
            $PlatformBit = PHP_OS ?: 'NA';
        }
    } elseif (strpos($UserAgent, 'Android') !== false) {
        $Platform = 'Android';
        if (preg_match('/Android ([\d.]+)/', $UserAgent, $Matches))
            $PlatformVersion = $M[1];
        else
            $PlatformVersion = 'NA'; // Android version is not always included in the user agent
    } elseif (strpos($UserAgent, 'iOS') !== false) {
        $Platform = 'iOS';
        if (preg_match('/iOS ([\d.]+)/', $UserAgent, $Matches))
            $PlatformVersion = $matches[1];
        else
            $PlatformVersion = 'NA'; // iOS version is not always included in the user agent



            
// Determine bitness based on specific iOS devices and architectures
        // ...
    } else {
        $Platform = 'NA';
        $PlatformVersion = '0';
        $PlatformBit = 'NA';
    }

    return [
        'IP' => ClientIP(),
        'Browser' => $Browser,
        'Version' => $BV,
        'Platform' => $Platform,
        'PlatformVersion' => $PlatformVersion,
        'Bitness' => $PlatformBit
    ];
}

function GenerateUniqueID($MaxLen = 16): string { // Max Len 32 // Don't cahnge in feture
    $Lenght = $MaxLen > 32 ? 32 : $MaxLen;
    $UUID = (random_int(1111, 9999) . random_int(111, 999) . (((int) str_replace('.', '', $_SERVER['REMOTE_ADDR'])) % 10000 ) . ((int) str_replace('.', '', microtime(true)) % 1000000)) . ( $Lenght > 16 ? GenerateUniqueID(16) : '');
    return substr($UUID, 0, $Lenght); // Note - never set (int) for this return because it's always same no unique 
}

function GenerateUUID(): string {

    $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();
    return substr($uuid, 0, $length);

    $randomPart1 = random_int(0, PHP_INT_MAX);
    $randomPart2 = random_int(0, PHP_INT_MAX);
    $timestamp = microtime(true);
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $combinedString = $randomPart1 . $randomPart2 . $timestamp . $ipAddress;
    $uniqueId = hash('sha256', $combinedString);
    return $uniqueId;
}

function ArrayToString($array) {
    $string = '';
    foreach ($array as $key => $value) {
        $string .= "$key: $value, ";
    }
    return rtrim($string, ', '); // Remove the trailing comma and space
}

function set_subdomain_cookie($name, $value, $expire, $path, $domain, $secure = false, $httponly = false) {
    $subdomain = SubDomain();
    $cookie_name = $subdomain . '_' . $name;
    setcookie($cookie_name, $value, $expire, $path, $domain, $secure, $httponly);
    ini_set('session.cookie_domain', '.example.com');
    ini_set('session.cookie_httponly', true);
    ini_set('session.use_only_cookies', true);
}

function start_subdomain_session() {
    $subdomain = SubDomain();
    session_name($subdomain . '_session_id');
    session_start();
}

<?php

use System\App\View;
use System\Preload\DBExc;
use System\Preload\Exc;
use System\Preload\SystemExc;

function vd($D, $E = 0)
{
    var_dump($D);
    if ($E == 1)
        exit();
}

function sdd(...$args)
{ // it's only for framwork debuging
    //return dd(...$args);
}

function dd($V, $E = 0)
{
    echo !is_array($V) ? '' : '<pre>';
    echo ('</br>');
    if (!is_array($V))
        echo $V ?: 'empty';
    else
        var_dump($V ?: 'empty');
    echo !is_array($V) ? '' : '</pre>';
    echo ('</br>');
    if ($E == 1)
        exit();
}

function env(string $K)
{
    return isset($K) ? (isset($_ENV[$K]) ? $_ENV[$K] : throw new SystemExc('not found '.$K .' in env file')) : null;
}

function FuncCallFrom()
{
    $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    return $caller = isset($dbt[2]['function']) ? (isset($dbt[2]['class']) ? trim(str_replace('\\', '/', ($dbt[2]['file'])), Root) . '@' . ($dbt[2]['class'] . $dbt[2]['type']) : '') . $dbt[2]['function'] : null;
}

//function Response($S, $M, $D, Exc|DBExc|Exception|SystemExc|null $E = null) {
function Response($S, $M, $D, $E = null)
{
    /*     */
    View(
        'Response',
        [
            'Status' => $S,
            'Msg' => $M,
            'Data' => $D,
            'SubView' => (View('About', ['Status' => $S], true)),
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
function View(string $File = '', array $D = [], bool $ReturnContent = false): string|false|null
{
    echo !class_exists('System\App\View') ? include System . 'App' . DS . 'View.php' : '';
    if ($ReturnContent == true)
        return (new View($File, ($D ?: []), $ReturnContent, $IsForSytem = false))->Content();
    else
        echo (new View($File, ($D ?: []), $ReturnContent, $IsForSytem = false))->Render();
    return '';
}

function SystemView(string $File = '', array $D = [], bool $ReturnContent = false): string|false|null
{
    echo !class_exists('System\App\View') ? include System . 'App' . DS . 'View.php' : '';
    if ($ReturnContent == true)
        return (new View($File, ($D ?: []), $ReturnContent, $IsForSytem = true))->Content();
    else
        echo (new View($File, ($D ?: []), $ReturnContent, $IsForSytem = true))->Render();
    return '';
}

function GetClassVariableValue($CN = null, $Var = null)
{


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

function SubDomain()
{
    $host = $_SERVER['HTTP_HOST'];
    $parts = explode('.', $host);
    
    if (env('AppIsHostedOnSubDomain') == true)
        return isset($parts[0]) && count($parts) === 4 ? strtolower($parts[0]) : null;
    else
        return isset($parts[0]) && count($parts) === 3 ? strtolower($parts[0]) : null;
}

function BaseDomain()
{
    $host = $_SERVER['HTTP_HOST'];
    $parts = explode('.', $host);
    if (env('AppIsHostedOnSubDomain') == true)
        return isset($parts[1]) && isset($parts[2]) ?
     strtolower(!empty(SubDomain()) ? ($parts[1] . '.' . $parts[2] . '.' . $parts[3]) : ($parts[0] . '.' . $parts[1] . '.' . $parts[2])) : null;
    else
        return isset($parts[1]) ? strtolower(!empty(SubDomain()) ? ($parts[1] . '.' . $parts[2]) : ($parts[0] . '.' . $parts[1])) : null;
}

function Domain()
{
    return $_SERVER['HTTP_HOST'];
}

function ClientIP($IPV6 = true)
{
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

function Client()
{
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

function GenerateUniqueID($MaxLen = 16): string
{ // Max Len 32 // Don't cahnge in feture
    $Lenght = $MaxLen > 32 ? 32 : $MaxLen;
    $UUID = (random_int(1111, 9999) . random_int(111, 999) . (((int) str_replace('.', '', $_SERVER['REMOTE_ADDR'])) % 10000) . ((int) str_replace('.', '', microtime(true)) % 1000000)) . ($Lenght > 16 ? GenerateUniqueID(16) : '');
    return substr($UUID, 0, $Lenght); // Note - never set (int) for this return because it's always same no unique 
}

function GenerateUUID(): string
{

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

function ArrayToString($array)
{
    $string = '';
    foreach ($array as $key => $value) {
        $string .= "$key: $value, ";
    }
    return rtrim($string, ', '); // Remove the trailing comma and space
}

function set_subdomain_cookie($name, $value, $expire, $path, $domain, $secure = false, $httponly = false)
{
    $subdomain = SubDomain();
    $cookie_name = $subdomain . '_' . $name;
    setcookie($cookie_name, $value, $expire, $path, $domain, $secure, $httponly);
    ini_set('session.cookie_domain', '.example.com');
    ini_set('session.cookie_httponly', true);
    ini_set('session.use_only_cookies', true);
}

function start_subdomain_session()
{
    $subdomain = SubDomain();
    session_name($subdomain . '_session_id');
    session_start();
}



// Set up a global exception handler
function GlobalExceptionInit(Throwable $E)
{
    dd('Call ================= Global Exception Handler function - From Exc Class =  ' . get_class($E));
    if ($E instanceof PDOException) {
        try {
            dd('Call ____________This is a PDOException IF true ');
            throw new DBExc($E->getMessage() ?: 'Msg not defined', $E->getCode(), $E);
        } catch (DBExc $E) {
            $E->Response($E);
        }
    } elseif ($E instanceof SystemExc) {
        try {
            dd('Call ____________ This is a SystemExc IF true ');
            throw new SystemExc($E->getMessage() ?: 'Msg not defined', $E->getCode(), $E);
        } catch (SystemExc $E) {
            $E->Response($E);
        }
    } else if ($E instanceof Exc) {
        dd('Call ____________This is a Exc IF true');
        $E->Response($E);
    } else if ($E instanceof DBExc) {
        $E->Response($E);
        dd('Call ____________This is a DBExc IF true');
    } else if ($E instanceof Exception) {
        //Response($E->getCode(), $E->getMessage(), [], $E);
        dd('Call ____________This is a Exception IF true');
        dd('<h1>Normal Exception </h1>');
        dd('Code = ' . $E->getCode());
        dd('Msg = ' . $E->getMessage());
        dd('Msg = ' . $E->getFile() . '@' . $E->getLine());
        dd('<pre>');
        dd($E->getTraceAsString());
        dd('</pre>');
        //return $E->Response($E);
    } else {
        dd('Call ____________This is a Else IF true');
        dd('<h1>Else Exception </h1>');
        dd('Code = ' . $E->getCode());
        dd('Msg = ' . $E->getMessage());
        dd('Msg = ' . $E->getFile() . '@' . $E->getLine());
        dd('<pre>');
        dd($E->getTraceAsString());
        dd('</pre>');
        //Response($E->getCode(), $E->getMessage(), [], $E);
    }
    SaveErrorLogFile($E, get_class($E));
}

function SaveErrorLogFile($E, $EClassName = null) {
    $traceID = defined('TraceID') ? TraceID : '';
    $oldTraceID = defined('OldTraceID') ? OldTraceID : '';
    $timestamp = date('Y-m-d H:i:s');
    $msg = "TID-{$traceID} OTID-{$oldTraceID} [{$timestamp}] {$EClassName}#{$E->getCode()} -> {$E->getMessage()}||";

    $errorDir = TenantBaseDir . $_SERVER['Tenant']['ID'] . '/ErrorLog/';
    $errorFile = $errorDir . date('Y-m-d') . '.log';

    // Ensure the directory exists
    if (!is_dir($errorDir)) {
        mkdir($errorDir, 0777, true);
    }

    // Write to the error log file
    file_put_contents($errorFile, $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function SaveAccessLogFile($Browser) {
    $traceID = defined('TraceID') ? TraceID : '';
    $oldTraceID = defined('OldTraceID') ? OldTraceID : '';
    $timestamp = date('Y-m-d H:i:s');
    
    $C = Client();
    $msg = "TID-{$traceID} OTID-{$oldTraceID} [{$timestamp}] Req-{$_SERVER['REQUEST_METHOD']} URI {$_SERVER['REQUEST_URI']} IP-{$C['IP']} {$C['Browser']}#{$C['Version']} {$C['Platform']}#{$C['PlatformVersion']}-{$C['Bitness']}||";
    
    $accessDir = TenantBaseDir . $_SERVER['Tenant']['ID'] . '/AccessLog/';
    $accessFile = $accessDir . date('Y-m-d') . '.log';

    // Ensure the directory exists
    if (!is_dir($accessDir)) {
        mkdir($accessDir, 0777, true);
    }

    // Write to the access log file
    file_put_contents($accessFile, $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
}


set_exception_handler('GlobalExceptionInit');
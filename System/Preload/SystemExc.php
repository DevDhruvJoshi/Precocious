<?php

namespace System\Preload;

use Exception;

class SystemExc extends Exception {
    private $parameters;
    public static $Code;
    public static $Message;
    public static $File;
    public static $Line;
    public static $Trace;
    public static $TraceString;
    public static $Query;

    function __construct($message, $code = 0, Exception $PE = null) {
        parent::__construct($message, $code, $PE);
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        self::$Code = $this->getCode();
        self::$File = $this->getFile();
        self::$Message = $this->getMessage();
        self::$Line = $this->getLine();
        self::$Trace = $this->getTrace();
        self::$TraceString = $this->getTraceAsString();

    }

    public function Response($E) {
        //error_log($logMessage); // Example using error log
        SystemView('Exception/System', [
            'Code' => self::$Code,
            'Message' => self::$Message,
            'Trace' => self::$TraceString,
            'File' => self::$File,
            'Line' => self::$Line,
            'E' => $E,
        ]);
    }

    public function __destruct() {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
    }
}

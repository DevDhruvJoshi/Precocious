<?php

class DBExc extends PDOException {

    private $parameters;
    public static $Code;
    public static $Message;
    public static $File;
    public static $Line;
    public static $Trace;
    public static $TraceString;
    public static $Query;

    public function __construct($message, $code = '', PDOException $PE = null, $query = '', $parameters = []) {
        parent::__construct($message, ( is_int($code) && $code > 0 ? $code : 1045), $PE);
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        self::$Code = $this->GetCodeFromMsg($PE->getMessage());
        self::$File = $PE->getFile();
        self::$Message = $PE->getMessage();
        self::$Line = $PE->getLine();
        self::$Trace = $PE->getTrace();
        self::$TraceString = $PE->getTraceAsString();
        self::$Query = $query;
        $this->parameters = $parameters;
    }

    public function getQuery() {
        return $this->query;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function Response($E) {
        SystemView('Exception/DB', [
            'Code' => self::$Code,
            'Message' => self::$Message,
            'Trace' => self::$TraceString,
            'Query' => htmlspecialchars(self::$Query),
            'File' => self::$File,
            'Line' => self::$Line,
            'E' => $E,
        ]);
    }

    function GetCodeFromMsg($message = null) {
        $pattern = '/SQLSTATE\[(\w{5})\]/';

        if (preg_match($pattern, $message, $matches)) {
            return $matches[1]; // Return the SQLSTATE code
        }

        return null; // Return null if no code is found
    }

    public function __destruct() {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
    }
}

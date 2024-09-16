<?php

namespace System\Preload;

use Exception;
use PDOException;
use Throwable;

/*
 * This is new Version of Exeption old is not capable for fetal error
 */

class Exceptions extends Exception {

    function __construct($message, $code = 0, Exception $PE = null) {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        parent::__construct($message, $code, $PE);
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        $this->Message = ($this->getMessage());
        $this->Code = ($this->getCode());
        $this->File = $this->getFile();
        $this->Line = $this->getLine();
        $this->Trace = $this->getTrace();
        $this->TraceString = $this->getTraceAsString();
    }

    function View($E) { // pending seperate exc wise seperate view 
        return View('Exception/Exc', [
            'Status' => $S,
            'Msg' => $M,
            'Data' => $D,
            //'SubView' => ( View('About', ['Status' => $S], true)),
            'Exc' => $E,
                ], false, $IsForSytem = true
        );
    }

    public function Response($E) {
        //error_log($logMessage); // Example using error log
        SystemView('Exception/Exc', [
            'Code' => $E->Code,
            'Message' => $E->Message,
            'Trace' => $this->TraceString,
            'File' => $this->File,
            'Line' => $this->Line,
            'E' => $E,
        ]);
    }

    function __destruct() {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
    }
}

/*  Start - Exception */


/*  End - Exception */
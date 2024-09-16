<?php

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

// Set up a global exception handler
function GlobalExceptionInit(Throwable $E) {
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
    //error_log($Msg, E_ERROR, $ErrorFile, 2); // issue with SMTP is reuired
    $Msg = ('TID-' . (( TraceID !== null ) ? TraceID: '') . ' OTID-' . OldTraceID . ' [' . date('Y-m-d H:i:s') . '] ' . $EClassName . '#' . $E->getCode() . ' -> ' . $E->getMessage()) . '||';
    $ErrorFile = ( TenantBaseDir . $_SERVER['Tenant']['ID'] . '/ErrorLog/' . date('Y-m-d')) . '.log';
//is_dir($ErrorFile) ?: throw new SystemExc('ErrorLog Directory is not found');
    file_exists($ErrorFile) ?: touch($ErrorFile);
    file_put_contents($ErrorFile, $Msg . PHP_EOL, FILE_APPEND);
}

function SaveAccessLogFile($Browser) {
    //error_log($Msg, E_ERROR, $ErrorFile, 2); // issue with SMTP is reuired
    $C = Client();
    $Msg = ('TID-' . TraceID . ' OTID-' . OldTraceID . ' [' . date('Y-m-d H:i:s') . '] Req-' . $_SERVER['REQUEST_METHOD'] . ' URI ' . $_SERVER['REQUEST_URI'] . ' IP-' . $C['IP'] . ' ' . $C['Browser'] . '#' . $C['Version'] . ' ' . $C['Platform'] . '#' . $C['PlatformVersion'] . '-' . $C['Bitness']) . '||';
    $ErrorFile = ( TenantBaseDir . $_SERVER['Tenant']['ID'] . '/AccessLog/' . date('Y-m-d')) . '.log';
//is_dir($ErrorFile) ?: throw new SystemExc('AccessLog Directory is not found');
    file_exists($ErrorFile) ?: touch($ErrorFile);
    file_put_contents($ErrorFile, $Msg . PHP_EOL, FILE_APPEND);
}

set_exception_handler('GlobalExceptionInit');

/*  End - Exception */
<?php

class SystemExc extends Exception {

    function __construct($message, $code = 0, Exception $PE = null) {
        parent::__construct($message, $code, $PE);
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        $this->Message = ($this->getMessage());
        $this->Code = ($this->getCode());
        $this->File = $this->getFile();
        $this->Line = $this->getLine();
        $this->Trace = $this->getTrace();
        $this->TraceString = $this->getTraceAsString();
    }

    public function Response($E) {
        //error_log($logMessage); // Example using error log
        SystemView('Exception/System', [
            'Code' => $E->Code,
            'Message' => $E->Message,
            'Trace' => $this->TraceString,
            'File' => $this->File,
            'Line' => $this->Line,
            'E' => $E,
        ]);
    }

    public function __destruct() {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
    }
}

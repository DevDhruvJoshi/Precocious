<?php

namespace System;
class Model {

    
     function __construct() {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
    }
    
    public static function __callStatic($name, $arguments) {
         dd($name);
    }

    static function Get() {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
        return [23232,234,234,234,234,324];
    }

     function __destruct() {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
    }

}

function __call($F, $Param) {// Added by Dhruv Joshi for Sheperate Function by get Fields From ID 
    if (in_array($F, $this->_describe)) {
        if (($ID = $Param[0]) > 0 || (!$ID > 0)) {
            $Q = $ID > 0 ? ('' . $this->_id . '=' . $ID) : ' 1=1 ';
            $Q .= !empty($Param[1]) ? (' and ' . $Param[1]) : '';
            $this->search($F, $Q);
            if ($ID > 0)
                return $this->getDataRow(1)[$this->_model][$F];
            else {
                foreach ($this->getDataRow() As $S)
                    $D[$S[$this->_model][$this->_id]] = $S[$this->_model][$F];

                return $D;
            }
        }
    }
}

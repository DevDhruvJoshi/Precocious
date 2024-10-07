<?php

namespace System\App\Session;

use \System\Config\DB;

class DBBased implements \SessionHandlerInterface {

    private $DB;

    public function __construct() {  // Set a default empty string (recommended)
        //self::$DB === null ? self::$DB = new DB() : null;
        $this->DB = $this->DB === null ? new DB() : $this->DB;
    }

    /**
     * Returns the database connection object.
     *
     * @return DB The database connection object.
     */
    /*     * / when need to use static method but now i'ts working fine so dont need this
      public static function DB() { // Connection - Manage static and object call
      return (isset($this) ? self::$DB : ( new DB()));
      }
      /* */
    public function open(string $save_path, string $session_name): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function Read($id): string|false {
        $Res = $this->DB->Select('Sessions', ['data'], [
            'session_id' => $id,
            'IP' => ClientIP(),
            'Browser' => base64_encode($_SERVER['HTTP_USER_AGENT']),
        ]);
        return (!empty($Res) ? $Res[0]['data'] : '');
    }

    public function Write($id, $data): bool {
        $this->DB->Replace('Sessions', [
            'session_id' => $id,
            'data' => $data,
            'IP' => ClientIP(),
            'Browser' => base64_encode($_SERVER['HTTP_USER_AGENT']),
        ]);
        return true;
    }

    public function destroy($id): bool {
        $this->DB->Delete('Sessions', ['session_id' => $id]);
        return true;
    }

    public function gc($maxlifetime) : int|false{
        $stmt = $this->DB->Query("DELETE FROM Sessions WHERE modified < DATE_SUB(NOW(), INTERVAL ? SECOND)", [$maxlifetime]);

        return 1;
    }
}

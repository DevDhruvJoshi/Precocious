<?php

namespace System\App\Tenant;

use System\Config\DB;
use System\App\Trait\UDFModel;

abstract class Base{

    use UDFModel;

    private static $DB;

    public function __construct() {  // Set a default empty string (recommended)
        self::$DB === null ? self::$DB = new DB(
                        env('DB_Host'),
                        env('DB_Name'),
                        env('DB_User'),
                        env('DB_Password'),
                        env('DB_Type'),
                        ) : null;
    }

    /**
     * Returns the database connection object.
     *
     * @return DB The database connection object.
     */
    public static function DB() { // Connection - Manage static and object call
        return (isset($this) ? self::$DB : ( new DB(
                        env('DB_Host'),
                        env('DB_Name'),
                        env('DB_User'),
                        env('DB_Password'),
                        env('DB_Type'),
        )));
    }
}

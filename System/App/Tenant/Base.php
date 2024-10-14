<?php

namespace System\App\Tenant;

use PDO;
use PDOException;
use System\Config\DB;
use System\App\Trait\UDFModel;
use System\Config\Credential;
use System\Preload\SystemExc;

abstract class Base
{

    use UDFModel;

    private static $DB;

    public function __construct()
    {  // Set a default empty string (recommended)
        self::$DB === null ? self::$DB = new DB(Credential::OwnerDB()) : null;
    }

    /**
     * Returns the database connection object.
     *
     * @return DB The database connection object.
     */
    public static function DB()
    { // Connection - Manage static and object call
        return (isset($this) ? self::$DB : (new DB(
            Credential::OwnerDB()
        )));
    }


}

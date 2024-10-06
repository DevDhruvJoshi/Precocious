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

    function CreateDatabase($dbName, $sqlFilePath) {
    
        // Database connection parameters
        $host = env('DB_HOST');
        $user = env('DB_USER');
        $password = env('DB_PASSWORD');
    
        try {
            // Step 1: Create a new PDO instance for MySQL
            $pdo = new PDO("mysql:host=$host", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // Step 2: Create Database
            $pdo->exec("CREATE DATABASE `$dbName`");
            echo "Database `$dbName` created successfully.<br>";
    
            // Step 3: Select the new database
            $pdo->exec("USE `$dbName`");
    
            // Step 4: Read SQL file and execute
            $sql = file_get_contents($sqlFilePath);
            $sqlQueries = explode(';', $sql);
            foreach ($sqlQueries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $pdo->exec($query);
                }
            }
            echo "SQL file imported successfully.<br>";
    
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
}

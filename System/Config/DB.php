<?php

namespace System\Config;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use \System\App\Tenant;
use System\Preload\DBExc;
use System\Preload\SystemExc;
/**
 * Class DB
 * Handles database operations such as connection, querying, and CRUD operations.
 * Author: Dhruv Joshi
 * Created on: 27-July-2024
 */

class DB
{

    private $Type = 'MySql';
    private $Host = 'localhost';
    private $DB = 'DMVC';
    private $User = 'root';
    private $Password = '';
    public $Connection;

    public function __construct($Host = null, $Name = null, $User = null, $Password = null, $Type = null)
    {
        try {

            if (Tenant::Permission() == true) {
                if (!empty($Host) && !empty($Name)) {
                    $this->Type = $Type;
                    $this->Host = $Host;
                    $this->DB = $Name;
                    $this->User = $User;
                    $this->Password = $Password;
                } else if (!empty(SubDomain())) {
                    $Tenant = (Tenant::DBCredencial(SubDomain()));
                    $this->Type = $Tenant['DB_Type'];
                    $this->Host = $Tenant['DB_Host'];
                    $this->DB = $Tenant['DB_Name'];
                    $this->User = $Tenant['DB_User'];
                    $this->Password = $Tenant['DB_Password'];
                }
            } else {
                $this->Type = env('DB_Type');
                $this->Host = env('DB_Host');
                $this->DB = env('DB_Name');
                $this->User = env('DB_User');
                $this->Password = env('DB_Password');
            }

            if (($Type = trim(strtolower($this->Type))) == 'mysql') {
                //$this->Connection = new PDO("$Type:host=$this->Host;dbname=$this->DB", $this->User, $this->Password); // direct connect with DBname but need to dynamic time issue so now flexible of db other wise use this direct but framwor isntall setup is not working
                $this->Connection = new PDO(strtolower($this->Type).":host=$this->Host", $this->User, $this->Password);
                $this->Connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                !empty($this->DB) ? $this->UseDB() : '';
            } else
                throw new SystemExc("Unsupported database type: " . $this->Type);
        } catch (PDOException $E) {
            throw new DBExc($E->getMessage(), $E->getCode(), $E);
        }
    }

    /**
     * Use a specific database after establishing a connection.
     *
     * @throws DBExc
     */
    private function UseDB()
    {
        try {
            $this->Connection->exec("USE `$this->DB`");
        } catch (PDOException $e) {
            throw new DBExc("UseDB error: " . $e->getMessage());
        }
    }


    /**
     * Check if a database exists.
     *
     * @param string $dbName The name of the database to check.
     * @return bool True if the database exists, false otherwise.
     * @throws DBExc
     *
     * Example:
     * $dbExists = $db->CheckDBExisted('test_db');
     */
    public function CheckDBExisted($dbName)
    {
        $sql = "SHOW DATABASES LIKE ?";
        $stmt = $this->Connection->prepare($sql);
        $stmt->execute([$dbName]);
        return $stmt->rowCount() > 0;
    }


    /**
     * Create a new database.
     *
     * @param string $dbName The name of the database to create.
     * @return bool True on success, false on failure.
     * @throws DBExc
     *
     * Example:
     * $db->CreateDB('new_database');
     */
    public function CreateDB($dbName)
    {
        $sql = "CREATE DATABASE `$dbName`";
        $stmt = $this->Connection->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Check if a table exists in the current database.
     *
     * @param string $tableName The name of the table to check.
     * @return bool True if the table exists, false otherwise.
     * @throws DBExc
     *
     * Example:
     * $tableExists = $db->CheckTableExisted('users');
     */
    public function CheckTableExisted($tableName)
    {
        $sql = "SHOW TABLES LIKE ?";
        $stmt = $this->Connection->prepare($sql);
        $stmt->execute([$tableName]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Create a new table in the current database.
     *
     * @param string $tableName The name of the table to create.
     * @param array $columns Array of column definitions (e.g., ['id INT PRIMARY KEY', 'name VARCHAR(255)']).
     * @return bool True on success, false on failure.
     * @throws DBExc
     *
     * Example:
     * $db->CreateTable('users', ['id INT PRIMARY KEY', 'name VARCHAR(255)']);
     */
    public function CreateTable($tableName, $columns)
    {
        $columnDefs = implode(", ", $columns);
        $sql = "CREATE TABLE `$tableName` ($columnDefs)";
        $stmt = $this->Connection->prepare($sql);
        return $stmt->execute();
    }
    /**
     * Run a large SQL file containing multiple statements.
     *
     * @param string $filePath The path to the SQL file.
     * @return bool True on success.
     * @throws Exception|DBExc
     *
     * Example:
     * $db->RunBigSQLFile('path/to/script.sql');
     */
    public function RunBigSQLFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("SQL file does not exist: $filePath");
        }

        $sql = file_get_contents($filePath);
        $statements = explode(';', $sql);

        foreach ($statements as $statement) {
            $trimmed = trim($statement);
            if (!empty($trimmed)) {
                $stmt = $this->Connection->prepare($trimmed);
                $stmt->execute();
            }
        }

        return true; // Indicate success
    }


    /**
     * Insert data into a specified table.
     *
     * @param string $table The name of the table.
     * @param array $data Associative array of data to insert (e.g., ['name' => 'John', 'age' => 30]).
     * @return int The ID of the inserted row.
     * @throws DBExc
     *
     * Example:
     * $insertId = $db->Insert('users', ['name' => 'John', 'age' => 30]);
     */
    public function Insert($table, array $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        try {
            $stmt = $this->Connection->prepare($sql);
            $I = 1;
            foreach ($data as $index => $value)
                $stmt->bindValue($I++, $value);
            $stmt->execute();
            return $this->Connection->lastInsertId(); // Return the inserted ID (if applicable)
        } catch (PDOException $e) {
            throw new DBExc("Insert error: " . $e->getMessage());
        }
    }

    /**
     * Replace data in a specified table.
     *
     * @param string $table The name of the table.
     * @param array $data Associative array of data to replace (e.g., ['id' => 1, 'name' => 'Doe']).
     * @return int The ID of the replaced row.
     * @throws DBExc
     *
     * Example:
     * $db->Replace('users', ['id' => 1, 'name' => 'Doe']);
     */
    public function Replace($table, array $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $sql = "REPLACE INTO $table ($columns) VALUES ($placeholders)";

        try {
            $stmt = $this->Connection->prepare($sql);
            $stmt->execute(array_values($data));
            $I = 1;
            foreach ($data as $index => $value)
                $stmt->bindValue($I++, $value);
            return $this->Connection->lastInsertId(); // Return the inserted ID (if applicable)
        } catch (PDOException $e) {
            throw new DBExc("Insert error: " . $e->getMessage());
        }
    }

    /**
     * Update data in a specified table based on conditions.
     *
     * @param string $table The name of the table.
     * @param array $data Associative array of data to update.
     * @param array $where Associative array of conditions (e.g., ['id' => 1]).
     * @return int The number of affected rows.
     * @throws DBExc
     *
     * Example:
     * $affectedRows = $db->Update('users', ['name' => 'Jane'], ['id' => 1]);
     */
    public function Update($table, array $data, array $where)
    {
        $set_clauses = array();
        $params = array();

        // Build SET clauses with named placeholders
        foreach ($data as $column => $value) {
            $set_clauses[] = "$column = :$column";
            $params[':' . $column] = $value;
        }
        $set_string = implode(', ', $set_clauses);

        // Build WHERE clauses with named placeholders
        $where_clauses = array();
        foreach ($where as $column => $WValue) {
            if (is_array($WValue)) {
                // Handle array value (IN clause)
                $placeholderCount = 0;
                $placeholders = array();
                foreach ($WValue as $key => $val) {
                    $placeholderCount++;
                    $params[':' . $column . '_placeholder_' . $placeholderCount] = $val;
                    $placeholders[] = ':' . $column . '_placeholder_' . $placeholderCount;
                }
                $where_clauses[] = "$column IN (" . implode(',', $placeholders) . ")";
            } else {
                // Handle single value with empty check
                if (!empty($WValue)) {
                    $where_clauses[] = "$column = :$column";
                    $params[':' . $column] = $WValue;
                }
            }
        }

        // Separate the ID condition from other conditions
        $idCondition = null;
        $otherConditions = [];
        foreach ($where_clauses as $clause) {
            if (strpos($clause, 'ID') !== false) {
                $idCondition = $clause;
            } else {
                $otherConditions[] = $clause;
            }
        }

        // Build the WHERE clause with the ID condition as a named placeholder
        $where_string = implode(' AND ', $otherConditions);
        if (!empty($idCondition)) {
            $where_string .= "  $idCondition";
        }

        // Build the complete SQL query with named placeholders
        $sql = "UPDATE $table SET $set_string WHERE $where_string";
        try {
            $stmt = $this->Connection->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new DBExc("Update error: " . $e->getMessage());
        }
    }

    /**
     * Select data from a specified table with optional filters and sorting.
     *
     * This method retrieves data using various conditions, joins, grouping, and ordering options.
     *
     * @param string $table The name of the table to select from.
     * @param array $columns The columns to select (e.g., ['users.name', 'orders.total']).
     * @param array|string|null $where Optional associative array of conditions (e.g., ['users.is_active' => 1, 'orders.created_at >' => '2023-01-01']).
     * @param array $join Optional array of join conditions (e.g., ['orders' => 'users.id = orders.user_id']).
     * @param array $groupBy Optional array of columns to group by (e.g., ['users.name']).
     * @param array $having Optional array of having conditions (e.g., ['COUNT(*) > 5']).
     * @param array|string|null $orderBy Optional array for ordering the results (e.g., ['orders.total' => 'DESC']).
     * @param int|null $limit Optional limit for the number of results (e.g., 10).
     * @param int|null $offset Optional offset for the results (e.g., 0).
     * @return array The fetched data.
     * @throws DBExc If there is an error during the query execution.
     *
     * Example usage:
     * $columns = ['users.name', 'orders.total'];
     * $where = ['users.is_active' => 1, 'orders.created_at >' => '2023-01-01'];
     * $join = ['orders' => 'users.id = orders.user_id'];
     * $groupBy = ['users.name'];
     * $having = ['COUNT(*) > 5'];
     * $orderBy = ['orders.total' => 'DESC'];
     * $limit = 10;
     * $offset = 0;
     * $results = $db->Select($table, $columns, $where, $join, $groupBy, $having, $orderBy, $limit, $offset);
     * Pending $Where need always array but not working != in this function 
     */

    public function Select(string $table, array $columns, array|string $where = null, array $join = [], array $groupBy = [], array $having = [], array|string $orderBy = null, int $limit = null, int $offset = null)
    {
        $select_string = implode(', ', $columns);

        $from_string = $table;

        $join_string = '';
        if (!empty($join)) {
            foreach ($join as $joinTable => $joinCondition) {
                $join_string .= " JOIN $joinTable ON $joinCondition";
            }
        }

        $where_string = '';
        if (!empty($where)) {

            if (is_array($where)) {

                $where_clauses = array();
                foreach ($where as $column => $value) {
                    if (is_array($value)) {
                        $placeholders = array_fill(0, count($value), '?');
                        $where_clauses[] = "$column IN (" . implode(',', $placeholders) . ")";
                    } else {
                        if ($value === '!=') {
                            // Handle "!=" condition
                            $where_clauses[] = "$column != ?";
                        } else {
                            $where_clauses[] = "$column = ?";
                        }
                    }
                }
                $where_string = ' WHERE ' . implode(' AND ', $where_clauses);
            } else {
                $where_string = ' WHERE ' . $where;
            }
        }

        $group_by_string = '';
        if (!empty($groupBy)) {
            $group_by_string = ' GROUP BY ' . implode(', ', $groupBy);
        }

        $having_string = '';
        if (!empty($having)) {
            $having_clauses = array();
            foreach ($having as $column => $value) {
                $having_clauses[] = "$column $value";
            }
            $having_string = ' HAVING ' . implode(' AND ', $having_clauses);
        }

        $order_by_string = '';
        if (!empty($orderBy)) {
            $order_by_clauses = array();
            foreach ($orderBy as $column => $direction) {
                $order_by_clauses[] = "$column $direction";
            }
            $order_by_string = ' ORDER BY ' . (is_array($orderBy) ? implode(', ', $order_by_clauses) : $orderBy);
        }
        $limit_offset_string = '';
        if (!is_null($limit)) {
            $limit_offset_string = ' LIMIT ' . $limit;
            if (!is_null($offset)) {
                $limit_offset_string .= ' OFFSET ' . $offset;
            }
        }


        $sql = "SELECT $select_string FROM $from_string $join_string $where_string $group_by_string $having_string $order_by_string $limit_offset_string";
        try {
            $stmt = $this->Connection->prepare($sql);
            $params = array_values($where);
            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value, PDO::PARAM_STR); // Assuming all values are strings
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DBExc("SQL Select: " . $e->getMessage());
        }
    }


    /**
     * Delete data from a specified table based on conditions.
     *
     * @param string $table The name of the table.
     * @param array $where Associative array of conditions (e.g., ['id' => 1]).
     * @return int The number of deleted rows.
     * @throws DBExc
     *
     * Example:
     * $deletedRows = $db->Delete('users', ['id' => 1]);
     */
    public function Delete($table, array $where)
    {

        $where_clauses = [];
        $params = [];

        foreach ($where as $column => $value) {
            if (is_array($value)) {
                // Handle multiple values using IN operator
                $placeholders = array_fill(0, count($value), '?');
                $where_clauses[] = "$column IN (" . implode(',', $placeholders) . ")";
                $params = array_merge($params, $value);
            } else {
                // Handle single value
                $where_clauses[] = "$column = ?";
                $params[] = $value;
            }
        }

        $where_string = implode(' AND ', $where_clauses);
        $sql = "DELETE FROM $table WHERE $where_string";

        try {
            $stmt = $this->Connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount(); // Return the number of deleted rows
        } catch (PDOException $e) {
            throw new DBExc("Delete error: " . $e->getMessage());
        }
    }

    /**
     * Execute a custom SQL query.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params Optional array of parameters for the query.
     * @return PDOStatement The PDOStatement object for further processing.
     * @throws DBExc
     *
     * Example:
     * $stmt = $db->Query('SELECT * FROM users WHERE age > ?', [18]);
     */
    public function Query($sql, array $params = [])
    {
        try {
            $stmt = $this->Connection->prepare($sql);
            $I = 1;
            foreach ($params as $index => $value)
                $stmt->bindValue($I++, $value);
            $stmt->execute();
            return $stmt; // Return the PDOStatement object for further processing (e.g., fetchAll())
        } catch (PDOException $e) {
            exit($stmt->queryString);
            //throw new DBExc("Query error: " . $e->getMessage(), $e->getMessage(), $e);
        }
    }

    public static function Table($Table)
    {
        $DB = new DB();
        return $D = $DB->Query('select * from ' . $Table . ';');
    }
    /**
     * Fetch all results from a PDOStatement.
     *
     * @param PDOStatement $stmt The prepared statement.
     * @return array The fetched results.
     */
    public function Fetch($stmt)
    {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Escape a value for safe use in SQL.
     *
     * @param mixed $value The value to escape.
     * @return string The escaped value.
     */
    public function Escape($value)
    { // SQL injection need to use but pending impimention allover framwork
        return $this->Connection->quote($value);
    }

    public function __destruct()
    {
        /*         * /
          if ($this->Connection) {
          $this->Connection->close();
          }
          /* */
    }
}

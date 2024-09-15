<?php

namespace System\App\Trait;

trait UDFModel {

    /**
     * Retrieves the table name for the current model class.
     *
     * @throws Exception If the table name is not set in the model class.
     * @return string The table name.
     */
    public static function TableName() {
        return GetClassVariableValue(get_called_class(), '_Table') ?:
                throw new \SystemExc('Table name not set in model, add this line `public static $_Table = "' . /* get_called_class() */'Table_Name' . 's";`'); // Throw an exception for clarity
    }

    /**
     * (Abstract) Retrieves the primary key column name for the current model.
     *
     * This function should be overridden in your specific model class to provide the actual primary key column name.
     *
     * @throws Exception If the primary key is not defined in the model class.
     *
     * @return string The primary key column name for the model.
     */
    public static function PrimaryKey() {
        return GetClassVariableValue(get_called_class(), '_ID') ?:
                self::Fetch("SHOW KEYS FROM " . self::TableName() . " WHERE Key_name = 'PRIMARY'")[0]['Column_name'];
    }

    /**
     * Retrieves the column name for the "trash" flag (soft deletion).
     *
     * @throws Exception If the trash column name is not set and cannot be found in the database.
     * @return string|null The trash column name, or null if not configured.
     */
    public static function TrashKey() {
        return GetClassVariableValue(get_called_class(), '_Trash') ?:
                self::CheckColumnExisted('Deleted');
    }

    /**
     * Retrieves the column name to be used for soft deletion, if defined.
     * 
     * Checks for either `_TrashAt` or `DeletedAt` columns by default.
     * You can customize this behavior by overriding this method in your specific model class.
     *
     * @throws Exception If no trash column is defined.
     *
     * @return string|null The column name for soft deletion, or null if not found.
     */
    public static function TrashAtKey() {
        return GetClassVariableValue(get_called_class(), '_TrashAt') ?:
                self::CheckColumnExisted('DeletedAt');
    }

    /**
     * Executes a database query and fetches results.
     *
     * @param string $Query The SQL query to execute.
     *
     * @return array|null The fetched data from the query, or null on error.
     */
    public static function Fetch($Query = '', $Params = []) {
        return ($DB = self::DB())->Fetch($DB->Query(
                                $Query, $Params
        ));
    }

    /**
     * (Placeholder) Executes an SQL query and potentially returns results.
     * 
     * This function is marked as pending and its implementation might need to be completed based on your specific requirements.
     *
     * @param string $Query The SQL query to execute.
     *
     * @return mixed The result of the query (implementation details depend on your DB class).
     */
    public static function Sql($Query, $Params = []) { // pending
        return ($DB = self::DB())->Fetch($DB->Query(
                                $Query, $Params
        ));
    }

    /*
     * 
     * Start - Comon Functions 
     *      
     */

    /**
     * Checks if a specific column exists in the current model's table.
     *
     * @param string $C The column name to check.
     * @return string|null The column name if it exists, null otherwise.
     * @throws PDOException If an error occurs while querying the database.
     */
    public static function CheckColumnExisted($C = '') {
        try {
            return !empty($D = self::Fetch("SELECT * FROM information_schema.columns WHERE table_name = '" . self::TableName() . "' And Column_name = '" . $C . "'")) ? $D[0]['COLUMN_NAME'] : '';
        } catch (\DBExc $E) {
            $E->Response($E);
            //throw new Exc($E->getMessage(), $E->getCode(), $E);
        }
    }

    /*
     * Insert , Create, Save are same but everyone easy to access
     */

    /**
     * Inserts a new record into the database table associated with the model.
     * 
     * This function is essentially a wrapper for `Save()`.
     *
     * @param array $Data An associative array containing the data to be inserted.
     *                    Keys should correspond to table column names.
     * @return mixed The result of the `Save()` function, which is typically
     *                either the ID of the inserted record or an empty string.
     */
    public static function Insert(array $Data = []) {
        return self::Save($Data);
    }

    /**
     * Creates a new record using a variadic parameter list.
     * 
     * This function likely serves the same purpose as `Save()`, but using a
     * different syntax. It retrieves the called class name (the model class)
     * using `get_called_class()` and then calls `Save()` on that class with the
     * provided arguments.
     *
     * @param array $Data An associative array containing the data to be saved.
     *                    Keys should correspond to table column names.
     * @return mixed The result of the database connection's `Insert()` or
     *                `Update()` method, depending on the operation performed.
     */
    public static function Create(...$args) { // This is replicate of Save() function 
        (get_called_class())::Save(...$args);
    }

    /**
     * Creates a new record or updates an existing record in the database table.
     * 
     * This is the core function for data persistence. It checks if the provided
     * data is empty. If not, it calls the database connection's `Insert()` method
     * for a new record or `Update()` for an existing record based on the presence
     * of an ID in the data.
     *
     * @param array $Data An associative array containing the data to be saved.
     *                    Keys should correspond to table column names.
     * @return mixed The result of the database connection's `Insert()` or
     *                `Update()` method, depending on the operation performed.
     */
    public static function Save(array $Data = []) {
        return !empty($Data) ? self::DB()->Insert(self::TableName(),
                        $Data) : '';
    }

    /**
     * Replace or create a new record or updates an existing record in the database table.
     * 
     * This is the core function for data persistence. It checks if the provided
     * data is empty. If not, it calls the database connection's `Insert()` method
     * for a new record or `Update()` for an existing record based on the presence
     * of an ID in the data.
     *
     * @param array $Data An associative array containing the data to be saved.
     *                    Keys should correspond to table column names.
     * @return mixed The result of the database connection's `Insert()` or
     *                `Update()` method, depending on the operation performed.
     */
    public static function Replace(array $Data = []) {
        return !empty($Data) ? self::DB()->Replace(self::TableName(),
                        $Data) : '';
    }

    /*
     * Insert , Create, Save are same but everyone easy to access
     */

    /**
     * Updates existing records in the database table.
     *
     * This function allows updating records based on provided data and either
     * IDs or a WHERE clause. It checks if the data is empty. If not, it calls
     * the database connection's `Update()` method with the table name, data to
     * update, and a condition based on either IDs or a WHERE clause.
     *
     * @param array $Data An associative array containing the data to be updated.
     *                    Keys should correspond to table column names.
     * @param mixed $IDs Either a single ID or an array of IDs to identify records
     *                   to be updated.
     * @param string $Where A WHERE clause to specify the records to be updated.
     * @return mixed The result of the database connection's `Update()` method.
     */
    public static function Update(array $Data = [], int $IDs = null, $Where = []) {
        return !empty($Data) ? self::DB()->Update(self::TableName(), $Data,
                        !empty($IDs) ? [self::PrimaryKey() => $IDs] : $Where
                ) : '';
    }

    /**
     * Deletes records from the database table associated with the model.
     *
     * This function allows for deletion by either specifying specific IDs or using a WHERE clause for filtering.
     * 
     * @param mixed $IDs (optional) An array of IDs to delete, or null if using a WHERE clause.
     * @param array $Where (optional) An associative array representing the WHERE clause conditions.
     * @return string|bool Empty string on success, or an error message on failure.
     *
     * @throws Exception If no IDs or WHERE clause is provided, or if an error occurs during deletion.
     */
    public static function Delete($IDs = null, $Where = []) {
        return !empty($IDs) || !empty($Where) ?
                self::DB()->Delete(self::TableName(),
                        !empty($IDs) ? [self::PrimaryKey() => $IDs] : $Where
                ) : '';
    }

    /**
     * Performs a soft deletion by marking records as "deleted" in the database.
     *
     * This function assumes the existence of a dedicated "trash" column in the model's table.
     * 
     * @param mixed $IDs (optional) An array of IDs to soft-delete, or null if using a WHERE clause.
     * @param array $Where (optional) An associative array representing the WHERE clause conditions.
     * @return string|bool Empty string on success, or an error message on failure.
     *
     * @throws Exception If no IDs or WHERE clause is provided, or if the "trash" column is not set.
     */
    public static function SoftDelete($IDs = null, $Where = []) {
        if (!empty($IDs) || !empty($Where)) {
            empty($TrashKey = self::TrashKey()) ? throw new Exception('Trash colomn are not set in model, add this line `public static $_Tresh = "Deleted";`') : ''; // Throw an exception for clarity
            $Data[$TrashKey] = 1;
            if (!empty(self::TrashAtKey()))
                $Data[self::TrashAtKey()] = time();
            return !empty($Data) ? self::DB()->Update(self::TableName(), $Data,
                            !empty($IDs) ? [self::PrimaryKey() => $IDs] : $Where
                    ) : '';
        }
    }

    /**
     * Deletes records from the database table associated with the model.
     *
     * This function allows for deletion by either specifying specific IDs or using a WHERE clause for filtering.
     * 
     * @param mixed $IDs (optional) An array of IDs to delete, or null if using a WHERE clause.
     * @param array $Where (optional) An associative array representing the WHERE clause conditions.
     * @return string|bool Empty string on success, or an error message on failure.
     *
     * @throws Exception If no IDs or WHERE clause is provided, or if an error occurs during deletion.
     */
    public static function Remove(...$args) { // This is replicate of Delete() function 
        (get_called_class())::Delete(...$args);
    }

    /**
     * Selects records from the database table associated with the model.
     *
     * This function allows for specifying which columns to select, a WHERE clause, and an ORDER BY clause.
     * 
     * @param array $S (optional) An array of column names to select.
     * @param String|Array $W (optional) A string representing the WHERE clause.
     * @param Array $OrderBy (optional) A string representing the ORDER BY clause.
     * @return array|null An array of selected records on success, or null on failure.
     */
    public static function Select(array $S = [], $W = null, array|string $OrderBy = null, $Join = [], $GroupBy = [], $Limit = null) {
        return ($DB = self::DB())->Select(self::TableName(), $S, $W, $Join, $GroupBy, $Having = [], $OrderBy, $Limit); // it's pending to impliment for 
        return Self::Fetch(
                        $Sql = "SELECT " . ((implode(',', array_filter($S))) ?: '*') . " FROM " .
                        self::TableName() . '' .
                        (!empty($W) ? ('' . $WhereString) : '') . (!empty($OrderBy) ? ' Order By ' . $OrderBy : '')
                        , $where_clauses ?: []);
    }

    /**
     * Selects records from the database table associated with the model.
     *
     * This function allows for specifying which columns to select, a WHERE clause, and an ORDER BY clause.
     * 
     * @param array $S (optional) An array of column names to select.
     * @param string $W (optional) A string representing the WHERE clause.
     * @param string $OrderBy (optional) A string representing the ORDER BY clause.
     * @return array|null An array of selected records on success, or null on failure.
     */
    public static function SelectOnes(...$args) {
        $Rs = (get_called_class())::Select(...$args);
        return !empty($Rs) ? $Rs[0] : null;
    }

    /**
     * Fetches all records from the table associated with the model.
     *
     * @param array $S (Optional) An associative array of column names and their desired values for filtering.
     * @param string $OrderBy (Optional) A SQL ORDER BY clause for sorting results.
     * @param int $Deleted (Optional) A flag indicating whether to include or exclude deleted records (defaults to 0, excluding deleted).
     * @return array An array of associative arrays representing the fetched records.
     * 
     * @throws Exception If an error occurs during database interaction.
     */
    public static function All(array $S = [], array|string $OrderBy = null, $Deleted = 0) {
        return self::Select($S ?: ['*'], (
                        $Deleted != true && !empty(self::TrashKey()) ? [self::TrashKey() => '0'] : ''
                        ), $OrderBy);
    }

    /**
     * Fetches a specific record by its ID.
     *
     * @param int|null $ID The ID of the record to fetch.
     * @param array $S (Optional) An associative array of column names and their desired values for filtering.
     * @return array|null An associative array representing the fetched record, or null if not found.
     * 
     * @throws Exception If an error occurs during database interaction.
     */
    public static function ID($ID = null, $S = []) {
        return $ID > 0 ? self::Select($S, ' ID= ' . $ID) : null;
    }

    /**
     * Fetches multiple records based on an array of IDs.
     *
     * @param array|null $IDs An array of integer IDs for filtering.
     * @param array $S (Optional) An associative array of column names and their desired values for filtering.
     * @return array|null An array of associative arrays representing the fetched records, or null if none found.
     * 
     * @throws Exception If an error occurs during database interaction.
     */
    public static function IDs($IDs = null, $S = []) {
        return !empty(array_filter($IDs)) ? self::Select($S, ' ID in ( ' . implode(', ', array_filter($IDs)) . ')') : null;
    }
}

mene ek php me Precocious name ka MVC framework banaya hai jo user ko code karneme simple ho aur complex code bhi easyli kar sake aur scalable bhi hai aur speed work deta hai to me iski website ke home page me kya kya mention karu pura ek home page banake do

https://stackoverflow.com/questions/21721495/how-to-deploy-correctly-when-using-composers-develop-production-switch
https://packagist.org/packages/dhruvjoshi/precocious
Mail@DhruvJoshi password - Piyu00712
<?php

Route::post('/Contact', [App/Controller/UserController::class,'Store']);
Route::put('/Contact', [App/Controller/UserController::class,'Put']);
Route::patch('/Contact', [App/Controller/UserController::class,'Patch']);
Route::delete('/Contact', [App/Controller/UserController::class,'Remove']);
Route::options('/Contact', [App/Controller/UserController::class,'Option']);


public function Update($table, array $data, array $where) {
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
    $where_string = implode(' AND ', $where_clauses);

    // Build the complete SQL query with named placeholders
    $sql = "UPDATE $table SET $set_string WHERE $where_string";

    try {
        $stmt = $this->Connection->prepare($sql);

        // Bind parameters using a loop
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }

        $stmt->execute();

        return $stmt->rowCount();
    } catch (PDOException $e) {
        throw new Exception("Update error: " . $e->getMessage());
    }
}







function checkTenantSetup($subDomain) {
    // Load .env file
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }

    // Database connection parameters
    $host = getenv('DB_HOST');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    try {
        // Connect to the main database
        $pdo = new PDO("mysql:host=$host", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Step 1: Check if tenant exists
        $stmt = $pdo->prepare("SELECT * FROM Tenants WHERE SubDomain = :subDomain");
        $stmt->execute(['subDomain' => $subDomain]);

        if ($stmt->rowCount() === 0) {
            return "Tenant not found.";
        }

        // Step 2: Get tenant details
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        $dbName = $tenant['DB_Name'];

        // Step 3: Connect to tenant's database
        $tenantPDO = new PDO("mysql:host=$host;dbname=$dbName", $user, $password);
        $tenantPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Step 4: Check if required tables exist
        $result = $tenantPDO->query("SHOW TABLES LIKE 'Tenants'");
        if ($result->rowCount() === 0) {
            return "Tenant setup incomplete. Please run the setup again.";
        }

        return "Tenant setup is complete.";

    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Call the function with user input
echo checkTenantSetup('user1');




function createTenant($tenantName, $subDomain) {
    // Load .env file
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }

    // Database connection parameters
    $host = getenv('DB_HOST');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    // Log file path
    $logFile = __DIR__ . '/logs/tenant_creation.log';
    $errorFile = __DIR__ . '/logs/error.log';

    try {
        // Step 1: Check if subdomain already exists
        $pdo = new PDO("mysql:host=$host", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM Tenants WHERE SubDomain = :subDomain");
        $stmt->execute(['subDomain' => $subDomain]);
        if ($stmt->rowCount() > 0) {
            file_put_contents($errorFile, "Error: Tenant with this subdomain already exists.\n", FILE_APPEND);
            return "Tenant with this subdomain already exists.";
        }

        // Step 2: Check if virtual host is available
        $virtualHost = "/var/www/$subDomain"; // Example path
        if (!is_dir($virtualHost)) {
            mkdir($virtualHost, 0755, true);
            file_put_contents($logFile, "Created virtual host directory: $virtualHost\n", FILE_APPEND);
        } else {
            file_put_contents($errorFile, "Error: Virtual host directory already exists.\n", FILE_APPEND);
            return "Virtual host directory already exists.";
        }

        // Step 3: Create a new database for the tenant
        $dbName = strtolower($tenantName) . "_db"; // Custom DB name
        $pdo->exec("CREATE DATABASE `$dbName`");
        file_put_contents($logFile, "Database `$dbName` created successfully.\n", FILE_APPEND);

        // Step 4: Run SQL dump for the new database
        $sql = file_get_contents(__DIR__ . '/example.sql'); // Load SQL file
        $pdo->exec("USE `$dbName`");
        $sqlQueries = explode(';', $sql);
        foreach ($sqlQueries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $pdo->exec($query);
            }
        }
        file_put_contents($logFile, "SQL file imported successfully.\n", FILE_APPEND);

        // Step 5: Create necessary asset directories
        $assetsDir = "$virtualHost/assets";
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
            file_put_contents($logFile, "Created assets directory: $assetsDir\n", FILE_APPEND);
        }

        // Step 6: Insert tenant details into Tenants table
        $insertStmt = $pdo->prepare("INSERT INTO Tenants (Name, SubDomain, DB_Name) VALUES (:name, :subDomain, :dbName)");
        $insertStmt->execute(['name' => $tenantName, 'subDomain' => $subDomain, 'dbName' => $dbName]);

        return "Tenant created successfully.";

    } catch (PDOException $e) {
        file_put_contents($errorFile, "Database Error: " . $e->getMessage() . "\n", FILE_APPEND);
        return "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        file_put_contents($errorFile, "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        return "Error: " . $e->getMessage();
    }
}

// Call the function with user input
echo createTenant('Apple Inc.', 'user1');

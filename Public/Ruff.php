mene ek php me Precocious name ka MVC framework banaya hai jo user ko code karneme simple ho aur complex code bhi easyli kar sake aur scalable bhi hai aur speed work deta hai to me iski website ke home page me kya kya mention karu pura ek home page banake do

https://stackoverflow.com/questions/21721495/how-to-deploy-correctly-when-using-composers-develop-production-switch
https://packagist.org/packages/dhruvjoshi/precocious
Mail@DhruvJoshi password - Piyu00712



give me a proper advanced php classes can check all of my need. i'm using custom php framwork so how can validate all of this.

check system is a new installed using file steps.

these steps is only for MultyTenantcy == true user only 
1. check ../System and ../Tenant directory file add karne aur write ki permission hai ki nahi agar nahi to error throw karo.
2.  check System Directory is created or not if not so create ../System,
3. in this DIR Steps.txt is available or not is not so create MultiTenancy.txt file, ye file se pata chalega user ko konse konse step pendin hai aur jo baki ho to wo step run karna padega 
4. check database credential is available in .env and update with 0 in Step.txt
5. check this connection is working or not if working and update with 1 in MultiTenancy.txt 
6. check System DB is created or not if not so create DB and update with 2 in MultiTenancy.txt
7. check Sessions table is create or not if not so create Sessions table and and update with 3 in MultiTenancy.txt
7. check Tenant table is create or not if not so create Tenant table and and update with 4 in MultiTenancy.txt
8. check in Tenant table first row is available or not if not so add row (is for owner admin panel), and and update with 5 in MultiTenancy.txt
9. 



these steps is only for MultyTenantcy != true user only 
1. check ../System and ../Tenant directory file add karne aur write ki permission hai ki nahi agar nahi to error throw karo.
2.  check System Directory is created or not if not so create ../System/,
3. in this DIR NonMultiTenancy.txt is available or not is not so create NonMultiTenancy.txt file, ye file se pata chalega user ko konse konse step pendin hai aur jo baki ho to wo step run karna padega 
4. check database credential is available in .env and update with 0 in NonMultiTenancy.txt
5. check this connection is working or not if working and update with 1 in NonMultiTenancy.txt 
6. check System DB is created or not if not so create DB and update with 2 in NonMultiTenancy.txt
7. check Sessions table is create or not if not so create Sessions table and and update with 3 in NonMultiTenancy.txt
7. check Config table is create or not if not so create Config table and and update with 4 in NonMultiTenancy.txt
8. resctict if any one hit wit subdomain throw multytenancy is not activated please contact to support


PLEASE NOTE har bar ye check karnese acha agar hum stepko hi check karle to acha rahega kyuki jyada system pe load na aaye aur hur bar ye validate na karna pade aapko pata hi hai ki step ki file agar .eve me MultiTenancy true ho to MultiTenancy.txt aur off hoto NonMultiTenancy.txt

aur sub steps mere hisabse add kiye hai aurbhi koi add karne hoto wo kardena kyu ki user kabhi bhi mera ye custom framwork add kare to usko koi deficulti nahi aani chahiye.

mera system dhruvjoshi.dev ye public access ke liye hoga aur *.dhruvjoshi.dev ye sabdomain tenant honge aur usme admin.dhruvjoshi.dev tenant owner ke liye set karna hai jo hamne Tenant table me 1st row jo add ki hai wo aur sabhi teantn ka DB bhi alag honge


        CREATE TABLE `Tenants` (
          `ID` int(11) NOT NULL AUTO_INCREMENT,
          `Name` varchar(255) NOT NULL,
          `SubDomain` varchar(255) NOT NULL,
          `Status` int(11) DEFAULT 0,
          `DB_Type` varchar(255) DEFAULT NULL,
          `DB_Host` varchar(255) DEFAULT NULL,
          `DB_Name` varchar(255) DEFAULT NULL,
          `DB_User` varchar(255) DEFAULT NULL,
          `DB_Password` text DEFAULT NULL,
          `Deleted` int(1) DEFAULT 0,
          `DeletedAt` int(11) DEFAULT NULL,
          PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


        CREATE TABLE `Sessions` (
            `session_id` varchar(32) NOT NULL,
        `modified` timestamp NOT NULL DEFAULT current_timestamp(),
      `data` blob DEFAULT NULL,
      `IP` varchar(100) DEFAULT NULL,
      `Browser` text DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




        New 

        ye class ko pura reak karo aur every test case check karke ye batao ki kya ye proper steps hai ya nahi kuiki jab user 1st time url hit karta hai tab owner ka DB aur Default DB bhi nahi hoga aur koi file bhi nahi hongi owner kito file banneke bad DB use hosata hai to unko kese manage kare aur jyada DB ki jarurat naho to call bhi nahi karwana hai kyuki ye validate life time call hoga isiliye step manage karna hai taki system ko jobhi need hai wo automaticly prepaire hojaye 

        aur kuch step bhi add karne hai jese ki 
*. Tenant ke folder me 0 name ka folder check karke create karna hoga
*. aur 0 folder ke andar AccessLog aur ErrorLog ki dir bhi create karni hai 






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





private function ExecuteSteps(): void
{
    $promises = [];

    foreach (self::STEPS as $Step => $Description) {
        if (in_array($Step, $this->completedSteps)) {
            continue; // Skip already completed steps
        }

        $promises[$Step] = $this->executeStepAsync($Step);
    }

    // Create a Promise that resolves when all the individual promises resolve
    $allPromises = new Promise(function ($resolve, $reject) use ($promises) {
        $results = [];
        $remaining = count($promises);

        foreach ($promises as $Step => $promise) {
            $promise->then(function ($result) use ($Step, &$results, &$remaining, $resolve) {
                $results[$Step] = $result;
                $remaining--;

                // Resolve if all promises have resolved
                if ($remaining === 0) {
                    $resolve($results);
                }
            })->catch(function ($error) use ($Step, $reject) {
                // Reject immediately if any promise fails
                $reject(new SystemExc("Validation failed at step $Step: " . $error));
            });
        }
    });

    // Wait for all promises to resolve or reject
    try {
        $allPromises->wait(); // Assuming a wait function to resolve
    } catch (SystemExc $e) {
        throw $e; // Handle the error as needed
    }
}

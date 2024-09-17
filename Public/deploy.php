<h2>app.dhruvjoshi.dev server</h2>
<?php
//require_once '/var/www/html/deploy.php'; exit;

// Your secret token from GitHub Webhook setup
$secret = '4cbEmv3UQzs4DUPG8G4zyMhhjpSzQX387whdH0Ciwz8UPDgbE32UgRsjY8kYg7i0'; // Update this if you set a secret

// Verify the GitHub signature if secret is used
if (isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
    $hash = 'sha1=' . hash_hmac('sha1', file_get_contents('php://input'), $secret);
    if ($signature !== $hash) {
        http_response_code(403);
        exit;
    } else {
            }}{ {       // Path to the deploy script
        $scriptPath = '/home/ubuntu/deploy.sh';

        // Execute the deploy script and capture output
        $output = shell_exec("$scriptPath 2>&1");

        // Log the output to a file
        file_put_contents('/var/www/html/deploy.log', $output);

        // Output the result to the browser (optional)
        echo "<pre>$output</pre>";
    }
}

exit();



// Path to the deploy script and status file
$scriptPath = '/home/ubuntu/deploy.sh';
$statusFile = '/var/www/html/deploy_status.txt';
$logFile = '/var/www/html/deploy.log';

// Execute the deploy script
$output = shell_exec("$scriptPath 2>&1");

// Check the status of the deployment
if (file_exists($statusFile)) {
    $statusCode = trim(file_get_contents($statusFile));
    http_response_code($statusCode);
    switch ($statusCode) {
        case '200':
            $statusMessage = 'Deployment was successful.';
            break;
        case '500':
            $statusMessage = 'Deployment failed. Please check the logs for details.';
            break;
        default:
            $statusMessage = 'Unknown status. Please check the logs.';
            break;
    }
} else {
    $statusMessage = 'Deployment status file not found. Please check the deployment script.';
}

// Output the result to the browser
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .status {
            padding: 10px;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <h1>Deployment Result</h1>
    <div class="status <?php echo ($statusCode == '200') ? 'success' : 'error'; ?>">
        <p><?php echo $statusMessage; ?></p>
    </div>
    <h2>Deployment Log</h2>
    <pre><?php echo htmlspecialchars(file_get_contents($logFile)); ?></pre>
</body>

</html>
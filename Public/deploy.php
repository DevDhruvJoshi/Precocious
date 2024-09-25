<h2>app.dhruvjoshi.dev server</h2>



<?php
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
        // Get the payload
        $payload = json_decode(file_get_contents('php://input'), true);

        // Validate if the request is from GitHub
        if (isset($payload['ref']) && $payload['ref'] === 'refs/heads/dev') { // 'dev' branch ko check karein
            // Command to pull the latest code
            $output = null;
            $return_var = null;

            // Run the git pull command
            exec('cd /var/www/app.dhruvjoshi.dev/Public && git pull', $output, $return_var);

            echo '<pre>';
            var_dump($output);
            echo '</pre>';
            // Check if the command was successful
            if ($return_var === 0) {
                echo "Deployment successful.";
            } else {
                echo "Deployment failed: " . implode("\n", $output);
            }
        } else {
            echo "Invalid request.";
        }
    }
?>


























key: 
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
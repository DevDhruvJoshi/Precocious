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
            $output = [];
            $return_var = null;

            // Run the git pull command
            exec('cd /var/www/app.dhruvjoshi.dev/Public && git pull 2>&1', $output, $return_var); // Error output ko bhi capture karein

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
}
?>
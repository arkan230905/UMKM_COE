<?php
// BTKL Fix Deployment Script
// This script will deploy the latest BTKL controller fix

echo "<h2>BTKL Fix Deployment</h2>";

// Check if this is a deployment request
if ($_POST['deploy'] ?? false) {
    echo "<h3>Starting deployment...</h3>";
    
    // Change to project directory
    chdir('/var/www/html');
    
    echo "<p>1. Pulling latest changes from GitHub...</p>";
    $git_pull = shell_exec('git pull origin main 2>&1');
    echo "<pre>$git_pull</pre>";
    
    echo "<p>2. Clearing caches...</p>";
    
    $cache_clear = shell_exec('php artisan config:clear 2>&1');
    echo "<pre>$cache_clear</pre>";
    
    $cache_clear = shell_exec('php artisan cache:clear 2>&1');
    echo "<pre>$cache_clear</pre>";
    
    $cache_clear = shell_exec('php artisan route:clear 2>&1');
    echo "<pre>$cache_clear</pre>";
    
    $cache_clear = shell_exec('php artisan view:clear 2>&1');
    echo "<pre>$cache_clear</pre>";
    
    $cache_clear = shell_exec('php artisan optimize:clear 2>&1');
    echo "<pre>$cache_clear</pre>";
    
    echo "<p>3. Checking BTKL controller file...</p>";
    if (file_exists('app/Http/Controllers/MasterData/BtklController.php')) {
        echo "<p style='color: green;'>✅ BtklController.php exists</p>";
        
        // Show the current commit status
        $git_log = shell_exec('git log --oneline -1 2>&1');
        echo "<p>Current commit: <strong>$git_log</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ BtklController.php not found</p>";
    }
    
    echo "<h3 style='color: green;'>✅ Deployment completed!</h3>";
    echo "<p><a href='/master-data/btkl/create' target='_blank'>Test BTKL Page</a></p>";
    
} else {
    // Show deployment form
    echo "<form method='post'>";
    echo "<p>Click the button below to deploy the BTKL fix:</p>";
    echo "<input type='hidden' name='deploy' value='1'>";
    echo "<input type='submit' value='Deploy BTKL Fix' style='padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;'>";
    echo "</form>";
    
    echo "<hr>";
    echo "<h3>What this deployment does:</h3>";
    echo "<ul>";
    echo "<li>✅ Pulls latest changes from GitHub (commit 5d79f86)</li>";
    echo "<li>✅ Clears all Laravel caches</li>";
    echo "<li>✅ Fixes BTKL dropdown to exclude process names like 'Penggorengan'</li>";
    echo "<li>✅ Maintains proper employee count calculations</li>";
    echo "</ul>";
    
    echo "<h3>Current Status:</h3>";
    $current_commit = shell_exec('git log --oneline -1 2>&1');
    echo "<p>Latest commit: <code>$current_commit</code></p>";
}

echo "<hr>";
echo "<p><small><strong>⚠️ Security Note:</strong> Delete this file after deployment!</small></p>";
?>

<?php
// 🚨 IMMEDIATE SITE FIX - PRESENTATION MODE
echo "<h1>🚨 IMMEDIATE SITE FIX</h1>";
echo "<p>Fixing HTTP 500 error for presentation...</p>";

chdir('/var/www/html');

echo "<h2>Step 1: Clear All Laravel Caches</h2>";
$commands = [
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan route:clear', 
    'php artisan view:clear',
    'php artisan optimize:clear'
];

foreach ($commands as $cmd) {
    $output = shell_exec($cmd . ' 2>&1');
    echo "<p>✅ $cmd</p>";
}

echo "<h2>Step 2: Rebuild Caches</h2>";
$rebuild = [
    'php artisan config:cache',
    'php artisan route:cache',
    'php artisan view:cache'
];

foreach ($rebuild as $cmd) {
    $output = shell_exec($cmd . ' 2>&1');
    echo "<p>✅ $cmd</p>";
}

echo "<h2>Step 3: Fix Permissions</h2>";
shell_exec('chmod -R 755 storage/ 2>&1');
shell_exec('chmod -R 755 bootstrap/cache/ 2>&1');
shell_exec('chown -R www-data:www-data storage/ 2>&1');
echo "<p>✅ Permissions fixed</p>";

echo "<h2>Step 4: Restart Services</h2>";
shell_exec('sudo systemctl restart apache2 2>&1');
echo "<p>✅ Apache restarted</p>";

echo "<h2>Step 5: Test Site</h2>";
$test = @file_get_contents('http://localhost/', false, stream_context_create(['http' => ['timeout' => 3]]));

if ($test !== false) {
    echo "<p style='color: green; font-size: 18px;'>✅ SITE IS WORKING!</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Site still has issues</p>";
}

echo "<div style='background: #28a745; color: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h2>🎉 SITE FIX COMPLETED!</h2>";
echo "<p><strong>Test your site now:</strong></p>";
echo "<ul>";
echo "<li><a href='/' target='_blank' style='color: white;'>🌐 Main Site</a></li>";
echo "<li><a href='/master-data/btkl/create' target='_blank' style='color: white;'>📋 BTKL Page</a></li>";
echo "<li><a href='/btkl_presentation.php' target='_blank' style='color: white;'>🎯 Backup Presentation Page</a></li>";
echo "</ul>";
echo "</div>";

echo "<p><small>Delete this file after use</small></p>";
?>

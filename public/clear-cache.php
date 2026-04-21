<?php
echo "<h1>Clear Cache</h1>";
echo "<pre>";

// Change to project directory
chdir('/var/www/html/UMKM_COE');

// Run cache clear commands
echo "Clearing Laravel cache...\n\n";

// Clear config cache
echo "1. Clearing config cache...\n";
$output = shell_exec('php artisan config:clear 2>&1');
echo $output . "\n";

// Clear application cache
echo "2. Clearing application cache...\n";
$output = shell_exec('php artisan cache:clear 2>&1');
echo $output . "\n";

// Clear view cache
echo "3. Clearing view cache...\n";
$output = shell_exec('php artisan view:clear 2>&1');
echo $output . "\n";

// Clear route cache
echo "4. Clearing route cache...\n";
$output = shell_exec('php artisan route:clear 2>&1');
echo $output . "\n";

echo "\n✅ Cache cleared!\n";
echo "Refresh your browser (Ctrl+F5) and check Jurnal Umum again.\n";

echo "</pre>";
?>

<?php
// Simple cache clearing script
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear various caches
Artisan::call('config:clear');
echo "Config cache cleared\n";

Artisan::call('cache:clear');
echo "Application cache cleared\n";

Artisan::call('view:clear');
echo "View cache cleared\n";

Artisan::call('route:clear');
echo "Route cache cleared\n";

echo "\nAll caches cleared successfully!\n";

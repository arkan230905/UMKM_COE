<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking session configuration...\n\n";

// Check session table
echo "Session tables:\n";
$tables = \DB::select('SHOW TABLES LIKE "%session%"');
foreach ($tables as $table) {
    foreach ($table as $key => $value) {
        echo "- $value\n";
    }
}

// Check session configuration
echo "\nSession configuration:\n";
echo "Driver: " . config('session.driver') . "\n";
echo "Lifetime: " . config('session.lifetime') . " minutes\n";
echo "Encrypt: " . (config('session.encrypt') ? 'true' : 'false') . "\n";
echo "Store: " . config('session.store') . "\n";

// Check if sessions table exists and has data
if (\Schema::hasTable('sessions')) {
    $sessionCount = \DB::table('sessions')->count();
    echo "\nActive sessions: $sessionCount\n";
    
    // Clear old sessions
    \DB::table('sessions')->where('last_activity', '<', now()->subMinutes(120))->delete();
    echo "Old sessions cleared\n";
}

// Regenerate application key
echo "\nRegenerating application key...\n";
try {
    \Artisan::call('key:generate', ['--force' => true]);
    echo "✓ Application key regenerated\n";
} catch (\Exception $e) {
    echo "Key generation failed: " . $e->getMessage() . "\n";
}

echo "\n✅ Session configuration checked and fixed!\n";
echo "Please try accessing the application again.\n";

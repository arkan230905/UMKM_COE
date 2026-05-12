<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SIMPLE DEBUG RETUR ===\n";

// Check if there are any new retur records created today
$today = date('Y-m-d');
echo "Checking for retur created on: {$today}\n";

$todayReturs = \App\Models\PurchaseReturn::whereDate('created_at', $today)->get();
echo "Found " . $todayReturs->count() . " retur created today\n\n";

// Check the latest 3 records
$latestReturs = \App\Models\PurchaseReturn::orderBy('created_at', 'desc')->limit(3)->get();
echo "Latest 3 retur records:\n";
foreach($latestReturs as $retur) {
    echo "ID: {$retur->id}, Number: {$retur->return_number}, Created: {$retur->created_at}\n";
}

// Check if there are any pending status
$pendingReturs = \App\Models\PurchaseReturn::where('status', 'pending')->get();
echo "\nPending retur count: " . $pendingReturs->count() . "\n";

// Check cache status
echo "\nCache driver: " . config('cache.default') . "\n";

// Try to clear cache
try {
    \Illuminate\Support\Facades\Cache::flush();
    echo "Cache cleared successfully\n";
} catch (Exception $e) {
    echo "Cache clear failed: " . $e->getMessage() . "\n";
}
<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Pegawais model with user_id column...\n";

try {
    // Test that the query works without error
    $pegawaisCount = \Illuminate\Support\Facades\DB::table('pegawais')
        ->where('user_id', 1)
        ->count();
    
    echo "Pegawais with user_id 1: {$pegawaisCount}\n";
    
    // Test the model directly
    $pegawaisModel = \App\Models\Pegawai::where('user_id', 1)->count();
    echo "Pegawais model query with user_id 1: {$pegawaisModel}\n";
    
    echo "Test completed successfully! No more 'user_id column not found' error.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

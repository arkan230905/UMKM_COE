<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Pembelian Destroy ===\n";

try {
    // Simulate a user login
    $user = App\Models\User::first();
    if ($user) {
        Auth::login($user);
        echo "✓ User logged in: {$user->name}\n";
    }
    
    // Get a pembelian to test
    $pembelian = App\Models\Pembelian::first();
    if (!$pembelian) {
        echo "✗ No pembelian found to test\n";
        exit(1);
    }
    
    echo "Testing pembelian ID: {$pembelian->id}\n";
    echo "Nomor: {$pembelian->nomor_pembelian}\n";
    
    // Check related data before deletion
    $detailsBefore = $pembelian->pembelianDetails()->count();
    $pelunasanBefore = $pembelian->pelunasan()->count();
    
    echo "Before deletion:\n";
    echo "- Details: {$detailsBefore}\n";
    echo "- Pelunasan: {$pelunasanBefore}\n";
    
    // Test the destroy method
    $controller = new App\Http\Controllers\PembelianController();
    
    echo "\nCalling destroy method...\n";
    $response = $controller->destroy($pembelian->id);
    
    echo "Response type: " . get_class($response) . "\n";
    
    // Check if pembelian still exists
    $stillExists = App\Models\Pembelian::find($pembelian->id);
    if ($stillExists) {
        echo "✗ Pembelian still exists after destroy\n";
    } else {
        echo "✓ Pembelian successfully deleted\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nDebug completed.\n";
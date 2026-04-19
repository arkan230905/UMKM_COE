<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Test BTKL create method after final fix
echo "Testing BTKL create method after final Perbumbuan fix:\n";
echo "========================================================\n";

$controller = new \App\Http\Controllers\MasterData\BtklController();

try {
    $response = $controller->create();
    
    // Get the data passed to view
    $viewData = $response->getData();
    
    echo "Jabatan data passed to view:\n";
    echo "================================\n";
    
    foreach ($viewData['jabatanBtkl'] as $jabatan) {
        echo "Position: {$jabatan->nama} (ID: {$jabatan->id})\n";
        echo "  Category: {$jabatan->kategori}\n";
        echo "  Tarif: " . ($jabatan->tarif ?? 'N/A') . "\n";
        echo "  Employee count: {$jabatan->pegawai_count}\n";
        
        if (isset($jabatan->pegawais)) {
            echo "  Employees:\n";
            foreach ($jabatan->pegawais as $emp) {
                echo "    - {$emp->nama} (ID: {$emp->id})\n";
            }
        }
        echo "  --- \n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

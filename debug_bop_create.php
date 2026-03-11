<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG BOP CREATE METHOD ===" . PHP_EOL;

// Simulate the exact same query as in BopProsesController::create()
echo "1. Testing query untuk availableProses:" . PHP_EOL;

try {
    $availableProses = \App\Models\ProsesProduksi::whereDoesntHave('bopProses')
        ->where('kapasitas_per_jam', '>', 0) // Only processes with capacity
        ->orderBy('nama_proses')
        ->get();
    
    echo "   Jumlah hasil: " . $availableProses->count() . PHP_EOL;
    
    // Debug each process
    foreach($availableProses as $proses) {
        echo "   - ID: {$proses->id}, Kode: {$proses->kode_proses}, Nama: {$proses->nama_proses}" . PHP_EOL;
        echo "     Kapasitas: {$proses->kapasitas_per_jam}, BTKL: {$proses->tarif_btkl}" . PHP_EOL;
        echo "     Has BOP: " . ($proses->hasBop() ? 'YES' : 'NO') . PHP_EOL;
        echo PHP_EOL;
    }
    
    // Test if view can be rendered
    echo "2. Testing view rendering:" . PHP_EOL;
    
    // Check if view file exists
    $viewPath = resource_path('views/master-data/bop-proses/create.blade.php');
    echo "   View file exists: " . (file_exists($viewPath) ? 'YES' : 'NO') . PHP_EOL;
    
    // Test rendering with data
    try {
        $html = view('master-data.bop-proses.create', compact('availableProses'))->render();
        echo "   View rendered successfully, length: " . strlen($html) . " characters" . PHP_EOL;
        
        // Check if dropdown options are in the HTML
        if (strpos($html, 'PRO-001') !== false) {
            echo "   ✅ Dropdown options found in HTML" . PHP_EOL;
        } else {
            echo "   ❌ Dropdown options NOT found in HTML" . PHP_EOL;
        }
        
    } catch (Exception $e) {
        echo "   ❌ View rendering failed: " . $e->getMessage() . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Query failed: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== TESTING RELATIONSHIP ===" . PHP_EOL;

// Test the bopProses relationship
$allProcesses = \App\Models\ProsesProduksi::with('bopProses')->get();
echo "Total proses produksi: " . $allProcesses->count() . PHP_EOL;

foreach($allProcesses as $proses) {
    echo "Proses: {$proses->nama_proses}" . PHP_EOL;
    echo "  - BOP Proses exists: " . ($proses->bopProses ? 'YES (ID: ' . $proses->bopProses->id . ')' : 'NO') . PHP_EOL;
    echo "  - whereDoesntHave should include: " . ($proses->bopProses ? 'NO' : 'YES') . PHP_EOL;
    echo PHP_EOL;
}

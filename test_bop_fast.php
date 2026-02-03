<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST BOP CONTROLLER INDEX (FAST VERSION) ===\n";
$startTime = microtime(true);

try {
    $controller = new \App\Http\Controllers\MasterData\BopController();
    
    // Call index method
    $result = $controller->index();
    
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // in milliseconds
    
    echo "Controller index() executed successfully\n";
    echo "Execution time: " . number_format($executionTime, 2) . " ms\n";
    
    if (is_object($result) && method_exists($result, 'getData')) {
        $data = $result->getData();
        echo "Data type: " . gettype($data) . "\n";
        
        if (is_array($data)) {
            echo "prosesProduksis count: " . (isset($data['prosesProduksis']) ? $data['prosesProduksis']->count() : 0) . "\n";
            echo "bopLainnya count: " . (isset($data['bopLainnya']) ? $data['bopLainnya']->count() : 0) . "\n";
            echo "akunBeban count: " . (isset($data['akunBeban']) ? $data['akunBeban']->count() : 0) . "\n";
            
            echo "\n=== SAMPLE DATA ===\n";
            if (isset($data['prosesProduksis']) && $data['prosesProduksis']->count() > 0) {
                $proses = $data['prosesProduksis']->first();
                echo "Sample Proses: " . $proses->kode_proses . " - " . $proses->nama_proses . "\n";
            }
            
            if (isset($data['bopLainnya']) && $data['bopLainnya']->count() > 0) {
                $bop = $data['bopLainnya']->first();
                echo "Sample BOP: " . $bop->kode_akun . " - " . $bop->nama_akun . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

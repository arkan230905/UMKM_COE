<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST BTKL CONTROLLER ===\n";

try {
    $controller = new \App\Http\Controllers\MasterData\BtklController();
    
    // Test index method
    $result = $controller->index();
    
    echo "Controller index() executed successfully\n";
    
    if (is_object($result) && method_exists($result, 'getData')) {
        $data = $result->getData();
        echo "Data type: " . gettype($data) . "\n";
        
        if (is_array($data) && isset($data['btkls'])) {
            echo "BTKL count: " . $data['btkls']->count() . "\n";
            
            if ($data['btkls']->count() > 0) {
                $btkl = $data['btkls']->first();
                echo "Sample BTKL:\n";
                echo "- Kode: " . $btkl->kode_proses . "\n";
                echo "- Jabatan: " . ($btkl->jabatan->nama ?? 'No jabatan') . "\n";
                echo "- Employee count: " . ($btkl->jabatan->pegawais->count() ?? 0) . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

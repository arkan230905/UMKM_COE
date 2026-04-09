<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Testing COA lookup for Bahan Baku ID 5...\n\n";

$bahanBaku = \App\Models\BahanBaku::find(5);
if ($bahanBaku) {
    echo "Bahan Baku: {$bahanBaku->nama_bahan}\n";
    echo "COA Pembelian ID: {$bahanBaku->coa_pembelian_id}\n\n";
    
    // Test direct lookup
    echo "=== DIRECT COA LOOKUP ===\n";
    $coaDirect = \App\Models\Coa::find($bahanBaku->coa_pembelian_id);
    if ($coaDirect) {
        echo "Direct lookup SUCCESS: {$coaDirect->nama_akun} ({$coaDirect->kode_akun})\n";
    } else {
        echo "Direct lookup FAILED\n";
    }
    
    // Test relationship
    echo "\n=== RELATIONSHIP LOOKUP ===\n";
    $bahanBakuWithCoa = \App\Models\BahanBaku::with('coaPembelian')->find(5);
    if ($bahanBakuWithCoa->coaPembelian) {
        echo "Relationship lookup SUCCESS: {$bahanBakuWithCoa->coaPembelian->nama_akun} ({$bahanBakuWithCoa->coaPembelian->kode_akun})\n";
    } else {
        echo "Relationship lookup FAILED\n";
    }
    
    // Check if the coaPembelian relationship is defined correctly
    echo "\n=== CHECKING RELATIONSHIP DEFINITION ===\n";
    $reflection = new ReflectionClass($bahanBaku);
    $methods = $reflection->getMethods();
    
    foreach ($methods as $method) {
        if ($method->getName() === 'coaPembelian') {
            echo "Found coaPembelian method\n";
            $source = file_get_contents($reflection->getFileName());
            $lines = explode("\n", $source);
            $startLine = $method->getStartLine() - 1;
            $endLine = $method->getEndLine() - 1;
            
            echo "Method definition:\n";
            for ($i = $startLine; $i <= $endLine; $i++) {
                if (isset($lines[$i])) {
                    echo "  " . $lines[$i] . "\n";
                }
            }
            break;
        }
    }
    
} else {
    echo "Bahan Baku ID 5 not found\n";
}

echo "\nDone.\n";

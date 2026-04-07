<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$produksi = \App\Models\Produksi::find(8);

if ($produksi) {
    echo "Product ID for production 8: {$produksi->produk_id}\n";
    echo "Product name: {$produksi->produk->nama_produk}\n";
    
    // Check BOM Job Costing
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produksi->produk_id)->first();
    
    if ($bomJobCosting) {
        echo "BOM Job Costing ID: {$bomJobCosting->id}\n";
        
        $btkls = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
        echo "\nBTKL processes:\n";
        foreach ($btkls as $btkl) {
            echo "- {$btkl->nama_proses}: Rp " . number_format($btkl->subtotal, 0, ',', '.') . "\n";
        }
        
        // Check for typo and fix it
        $typoProcess = $btkls->where('nama_proses', 'Perbumbuan')->first();
        if ($typoProcess) {
            echo "\n⚠️  Found typo in source data: 'Perbumbuan'\n";
            echo "Fixing source data...\n";
            
            $typoProcess->update(['nama_proses' => 'Pembumbuan']);
            echo "✅ Fixed source BTKL data: 'Perbumbuan' → 'Pembumbuan'\n";
        }
    } else {
        echo "❌ BOM Job Costing not found\n";
    }
} else {
    echo "❌ Production not found\n";
}
<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== RESET BOM DATA TO ZERO (SEPERTI BELUM ADA INPUT) ===\n\n";

try {
    // Get current BomJobCosting data
    $jobCostings = \App\Models\BomJobCosting::with('produk')->get();
    
    echo "Data saat ini di bom_job_costings:\n";
    foreach ($jobCostings as $jc) {
        echo "ID: " . $jc->id . " - Produk: " . $jc->produk->nama_produk . "\n";
        echo "  Total BBB: " . $jc->total_bbb . "\n";
        echo "  Total BTKL: " . $jc->total_btkl . "\n";
        echo "  Total BOP: " . $jc->total_bop . "\n";
        echo "  Total HPP: " . $jc->total_hpp . "\n";
        echo "---\n";
    }
    
    echo "\nResetting data ke kondisi awal (seperti belum ada input)...\n\n";
    
    foreach ($jobCostings as $jc) {
        echo "Resetting BomJobCosting ID " . $jc->id . " (Produk: " . $jc->produk->nama_produk . ")\n";
        
        // Reset to zero
        $jc->total_bbb = 0;
        $jc->total_btkl = 0;
        $jc->total_bahan_pendukung = 0;
        $jc->total_bop = 0;
        $jc->total_hpp = 0;
        $jc->hpp_per_unit = 0;
        $jc->save();
        
        echo "  ✅ BomJobCosting reset to zero\n";
        
        // Delete detail records
        $btklCount = \App\Models\BomJobBTKL::where('bom_job_costing_id', $jc->id)->delete();
        $bopCount = \App\Models\BomJobBOP::where('bom_job_costing_id', $jc->id)->delete();
        
        echo "  ✅ Deleted " . $btklCount . " BTKL detail records\n";
        echo "  ✅ Deleted " . $bopCount . " BOP detail records\n";
        
        // Reset product harga_pokok
        $jc->produk->harga_pokok = 0;
        $jc->produk->save();
        
        echo "  ✅ Reset product harga_pokok to 0\n";
        echo "---\n";
    }
    
    echo "\n=== VERIFICATION AFTER RESET ===\n";
    
    $resetJobCostings = \App\Models\BomJobCosting::with('produk')->get();
    
    foreach ($resetJobCostings as $jc) {
        echo "ID: " . $jc->id . " - Produk: " . $jc->produk->nama_produk . "\n";
        echo "  Total BBB: " . $jc->total_bbb . "\n";
        echo "  Total BTKL: " . $jc->total_btkl . "\n";
        echo "  Total BOP: " . $jc->total_bop . "\n";
        echo "  Total HPP: " . $jc->total_hpp . "\n";
        echo "  HPP per Unit: " . $jc->hpp_per_unit . "\n";
        echo "  Product Harga Pokok: " . $jc->produk->harga_pokok . "\n";
        echo "---\n";
    }
    
    echo "\n✅ Semua data berhasil direset ke kondisi awal!\n";
    echo "Sekarang data akan menunjukkan 0 seperti belum ada input manual.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== RESET SELESAI ===\n";

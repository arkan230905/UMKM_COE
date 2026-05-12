<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 Updating existing production data...\n\n";

// Get all existing production records
$productions = \App\Models\Produksi::with(['proses', 'produk'])->get();

echo "Found {$productions->count()} production records\n\n";

foreach ($productions as $produksi) {
    echo "Processing Production ID: {$produksi->id} - {$produksi->produk->nama_produk}\n";
    
    // Get BOM Job Costing for this product
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produksi->produk_id)->first();
    
    if (!$bomJobCosting) {
        echo "  ❌ No BOM Job Costing found for this product\n";
        continue;
    }
    
    // Get BTKL processes
    $bomJobBTKLs = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
    
    if ($bomJobBTKLs->isEmpty()) {
        echo "  ❌ No BTKL processes found\n";
        continue;
    }
    
    echo "  📋 Found {$bomJobBTKLs->count()} BTKL processes\n";
    
    // Update existing processes with correct names
    foreach ($produksi->proses as $index => $proses) {
        $bomJobBTKL = $bomJobBTKLs->get($index);
        
        if ($bomJobBTKL) {
            $oldName = $proses->nama_proses;
            $newName = $bomJobBTKL->nama_proses ?? 'Proses ' . ($index + 1);
            
            $proses->update([
                'nama_proses' => $newName
            ]);
            
            echo "    ✅ Updated process {$proses->urutan}: '{$oldName}' → '{$newName}'\n";
        }
    }
    
    // Calculate and update BOP for each process
    $bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
    
    if ($bomJobBOPs->isEmpty()) {
        echo "  ⚠️  No BOP data found\n";
        continue;
    }
    
    echo "  💰 Found {$bomJobBOPs->count()} BOP components\n";
    
    // Group BOP by process name
    $bopByProcess = [];
    foreach ($bomJobBOPs as $bomJobBOP) {
        $namaProses = 'Umum';
        $namaBiaya = strtolower($bomJobBOP->nama_bop ?? '');
        
        // Map BOP components to process names based on naming convention
        if (stripos($namaBiaya, 'penggorengan') !== false || stripos($namaBiaya, 'goreng') !== false) {
            $namaProses = 'Penggorengan';
        } elseif (stripos($namaBiaya, 'pembumbuan') !== false || stripos($namaBiaya, 'bumbu') !== false) {
            $namaProses = 'Pembumbuan';
        } elseif (stripos($namaBiaya, 'pengemasan') !== false || stripos($namaBiaya, 'kemas') !== false) {
            $namaProses = 'Pengemasan';
        }
        
        if (!isset($bopByProcess[$namaProses])) {
            $bopByProcess[$namaProses] = 0;
        }
        $bopByProcess[$namaProses] += $bomJobBOP->subtotal ?? 0;
    }
    
    echo "  📊 BOP by process:\n";
    foreach ($bopByProcess as $processName => $bopAmount) {
        echo "    - {$processName}: Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
    }
    
    // Update BOP for each process
    foreach ($produksi->proses as $proses) {
        $bopAmount = 0;
        
        // Find matching BOP for this process
        foreach ($bopByProcess as $prosesName => $bopValue) {
            if (stripos($proses->nama_proses, $prosesName) !== false || 
                ($prosesName === 'Umum' && !isset($bopByProcess[$proses->nama_proses]))) {
                $bopAmount = $bopValue;
                break;
            }
        }
        
        $oldBop = $proses->biaya_bop;
        $newTotalBiaya = $proses->biaya_btkl + $bopAmount;
        
        $proses->update([
            'biaya_bop' => $bopAmount,
            'total_biaya_proses' => $newTotalBiaya,
        ]);
        
        echo "    ✅ Updated BOP for '{$proses->nama_proses}': Rp " . number_format($oldBop, 0, ',', '.') . " → Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
    }
    
    echo "  ✅ Production {$produksi->id} updated successfully\n\n";
}

echo "🎉 All production data updated!\n";
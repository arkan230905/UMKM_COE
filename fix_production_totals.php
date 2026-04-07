<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing production totals to match quantity...\n\n";

$produksi = \App\Models\Produksi::with(['proses', 'produk'])->find(8);

if (!$produksi) {
    echo "❌ Production not found\n";
    exit;
}

echo "Processing Production ID: {$produksi->id} - {$produksi->produk->nama_produk}\n";
echo "Qty Produksi: {$produksi->qty_produksi}\n\n";

// Get BOM Job Costing
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produksi->produk_id)->first();

if (!$bomJobCosting) {
    echo "❌ BOM Job Costing not found\n";
    exit;
}

// Get BOP data and group by process
$bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();

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
    // Multiply by production quantity to get total BOP for this production
    $bopByProcess[$namaProses] += ($bomJobBOP->subtotal ?? 0) * $produksi->qty_produksi;
}

echo "📊 BOP by process (total for production):\n";
foreach ($bopByProcess as $processName => $bopAmount) {
    echo "  - {$processName}: Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
}

echo "\n🔄 Updating process costs:\n";

// Update each process
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
    
    // Get BTKL per unit from BomJobBTKL and multiply by production quantity
    $bomJobBTKL = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)
        ->where('nama_proses', $proses->nama_proses)
        ->first();
    
    $btklPerUnit = $bomJobBTKL ? $bomJobBTKL->subtotal : 0;
    $btklAmount = $btklPerUnit * $produksi->qty_produksi;
    
    $oldBTKL = $proses->biaya_btkl;
    $oldBOP = $proses->biaya_bop;
    
    $proses->update([
        'biaya_btkl' => $btklAmount,
        'biaya_bop' => $bopAmount,
        'total_biaya_proses' => $btklAmount + $bopAmount,
    ]);
    
    echo "  ✅ {$proses->nama_proses}:\n";
    echo "    - BTKL: Rp " . number_format($oldBTKL, 0, ',', '.') . " → Rp " . number_format($btklAmount, 0, ',', '.') . "\n";
    echo "    - BOP: Rp " . number_format($oldBOP, 0, ',', '.') . " → Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
    echo "    - Total: Rp " . number_format($btklAmount + $bopAmount, 0, ',', '.') . "\n\n";
}

echo "🎉 Production totals fixed!\n";
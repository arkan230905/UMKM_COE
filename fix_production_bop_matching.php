<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing BOP matching for production processes...\n\n";

// Get production ID 8
$produksi = \App\Models\Produksi::with(['proses', 'produk'])->find(8);

if (!$produksi) {
    echo "❌ Production ID 8 not found\n";
    exit;
}

echo "Processing Production ID: {$produksi->id} - {$produksi->produk->nama_produk}\n";

// Get BOM Job Costing for this product
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produksi->produk_id)->first();

if (!$bomJobCosting) {
    echo "❌ No BOM Job Costing found for this product\n";
    exit;
}

// Get BOP data
$bomJobBOPs = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();

echo "💰 Found {$bomJobBOPs->count()} BOP components:\n";
foreach ($bomJobBOPs as $bop) {
    echo "  - {$bop->nama_bop}: Rp " . number_format($bop->subtotal, 0, ',', '.') . "\n";
}

// Group BOP by process name with better matching
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

echo "\n📊 BOP grouped by process:\n";
foreach ($bopByProcess as $processName => $bopAmount) {
    echo "  - {$processName}: Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
}

echo "\n🔄 Current processes:\n";
foreach ($produksi->proses as $proses) {
    echo "  - {$proses->nama_proses} (BTKL: Rp " . number_format($proses->biaya_btkl, 0, ',', '.') . ", BOP: Rp " . number_format($proses->biaya_bop, 0, ',', '.') . ")\n";
}

// Update BOP for each process with improved matching
echo "\n🔧 Updating BOP for each process:\n";
foreach ($produksi->proses as $proses) {
    $bopAmount = 0;
    $matchedProcess = 'None';
    
    // Improved matching logic
    $prosesName = strtolower($proses->nama_proses);
    
    if (stripos($prosesName, 'pembumbuan') !== false || stripos($prosesName, 'bumbu') !== false) {
        $bopAmount = $bopByProcess['Pembumbuan'] ?? 0;
        $matchedProcess = 'Pembumbuan';
    } elseif (stripos($prosesName, 'penggorengan') !== false || stripos($prosesName, 'goreng') !== false) {
        $bopAmount = $bopByProcess['Penggorengan'] ?? 0;
        $matchedProcess = 'Penggorengan';
    } elseif (stripos($prosesName, 'pengemasan') !== false || stripos($prosesName, 'kemas') !== false) {
        $bopAmount = $bopByProcess['Pengemasan'] ?? 0;
        $matchedProcess = 'Pengemasan';
    } else {
        // If no specific match, use 'Umum' or distribute remaining
        $bopAmount = $bopByProcess['Umum'] ?? 0;
        $matchedProcess = 'Umum';
    }
    
    $oldBop = $proses->biaya_bop;
    $newTotalBiaya = $proses->biaya_btkl + $bopAmount;
    
    $proses->update([
        'biaya_bop' => $bopAmount,
        'total_biaya_proses' => $newTotalBiaya,
    ]);
    
    echo "  ✅ '{$proses->nama_proses}' matched to '{$matchedProcess}': Rp " . number_format($oldBop, 0, ',', '.') . " → Rp " . number_format($bopAmount, 0, ',', '.') . "\n";
}

echo "\n🎉 BOP matching fixed!\n";
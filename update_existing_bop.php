<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$produksi = \App\Models\Produksi::find(3);
if (!$produksi) {
    die("Produksi not found\n");
}

echo "Updating BOP for Produksi ID: 3\n";
echo "Qty Produksi: " . $produksi->qty_produksi . "\n\n";

// Get BOP breakdown
$hppBops = \App\Models\HargaPokokProduksiBop::where('user_id', 2)
    ->with('bopProses')
    ->get();

$bopByNamaProses = [];
foreach ($hppBops as $hppBop) {
    if ($hppBop->bopProses) {
        $namaProses = $hppBop->bopProses->nama_bop_proses;
        $komponenBop = $hppBop->bopProses->komponen_bop;
        
        if (is_string($komponenBop)) {
            $komponenBop = json_decode($komponenBop, true) ?? [];
        }
        
        $totalBopPerProduk = $hppBop->bopProses->total_bop_per_produk ?? 0;
        
        if (!isset($bopByNamaProses[$namaProses])) {
            $bopByNamaProses[$namaProses] = 0;
        }
        $bopByNamaProses[$namaProses] += $totalBopPerProduk;
        
        echo "BOP Proses: $namaProses\n";
        echo "  Total BOP per Produk: $totalBopPerProduk\n";
    }
}

echo "\n=== Updating produksi_proses ===\n";
foreach ($bopByNamaProses as $namaProses => $bopPerUnit) {
    $proses = \App\Models\ProduksiProses::where('produksi_id', 3)
        ->where('nama_proses', 'LIKE', '%' . $namaProses . '%')
        ->first();
    
    if ($proses) {
        $biayaBop = $bopPerUnit * $produksi->qty_produksi;
        $proses->biaya_bop = $biayaBop;
        $proses->total_biaya_proses = $proses->biaya_btkl + $biayaBop;
        $proses->save();
        
        echo "✓ Updated: {$proses->nama_proses}\n";
        echo "  Biaya BOP: Rp " . number_format($biayaBop, 0, ',', '.') . "\n";
        echo "  Total Biaya Proses: Rp " . number_format($proses->total_biaya_proses, 0, ',', '.') . "\n";
    } else {
        echo "✗ Proses not found: $namaProses\n";
    }
}

echo "\nDone!\n";

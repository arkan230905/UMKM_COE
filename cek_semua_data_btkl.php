<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK SEMUA DATA BTKL DI SISTEM ===" . PHP_EOL;

// 1. Cek semua BomJobBTKL
echo "1. SEMUA BomJobBTKL:" . PHP_EOL;
$allBomJobBTKL = \App\Models\BomJobBTKL::with(['bomJobCosting.produk', 'btkl'])->get();
echo "Total: " . $allBomJobBTKL->count() . PHP_EOL . PHP_EOL;

foreach ($allBomJobBTKL as $btkl) {
    echo "Produk: " . ($btkl->bomJobCosting->produk->nama_produk ?? 'Unknown') . PHP_EOL;
    echo "  ID: {$btkl->id}" . PHP_EOL;
    echo "  Nama Proses: " . ($btkl->nama_proses ?? 'N/A') . PHP_EOL;
    echo "  BTKL ID: " . ($btkl->btkl_id ?? 'NULL') . PHP_EOL;
    echo "  BTKL Nama: " . ($btkl->btkl->nama ?? 'N/A') . PHP_EOL;
    echo "  Durasi: " . ($btkl->durasi_jam ?? 0) . " jam" . PHP_EOL;
    echo "  Tarif: Rp " . number_format($btkl->tarif_per_jam, 2, ',', '.') . "/jam" . PHP_EOL;
    echo "  Kapasitas: " . ($btkl->kapasitas_per_jam ?? 0) . " unit/jam" . PHP_EOL;
    echo "  Subtotal: Rp " . number_format($btkl->subtotal, 2, ',', '.') . PHP_EOL;
    echo "  Biaya per unit: ";
    
    $kapasitas = $btkl->kapasitas_per_jam ?? 1;
    $biayaPerUnit = $kapasitas > 0 ? $btkl->tarif_per_jam / $kapasitas : 0;
    echo "Rp " . number_format($biayaPerUnit, 2, ',', '.') . PHP_EOL;
    echo PHP_EOL;
}

// 2. Cek apakah ada tabel lain yang mungkin digunakan
echo "2. CEK TABEL LAIN YANG MUNGKIN DIGUNAKAN:" . PHP_EOL;

// Cek tabel btkl (master)
echo "Master BTKL:" . PHP_EOL;
$masterBTKL = \Illuminate\Support\Facades\DB::table('btkls')->get();
foreach ($masterBTKL as $btkl) {
    echo "- ID: {$btkl->id}, Nama: '{$btkl->nama}', Tarif: Rp " . 
         number_format($btkl->tarif_per_jam, 2, ',', '.') . "/jam" . PHP_EOL;
}

// Cek apakah ada tabel proses_produksi
echo PHP_EOL . "Proses Produksi:" . PHP_EOL;
try {
    $prosesProduksi = \Illuminate\Support\Facades\DB::table('proses_produksis')->get();
    echo "Total: " . $prosesProduksi->count() . PHP_EOL;
    foreach ($prosesProduksi as $proses) {
        echo "- ID: {$proses->id}, Nama: '{$proses->nama_proses}', Tarif: Rp " . 
             number_format($proses->tarif_per_jam ?? 0, 2, ',', '.') . "/jam" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Tabel proses_produksis tidak ada atau error: " . $e->getMessage() . PHP_EOL;
}

// 3. Cek per produk
echo PHP_EOL . "3. REKAP PER PRODUK:" . PHP_EOL;
$produkList = \App\Models\Produk::all();

foreach ($produkList as $produk) {
    echo "Produk: {$produk->nama_produk}" . PHP_EOL;
    
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
    if ($bomJobCosting) {
        $btklDetails = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
        
        $totalBiayaPerUnit = 0;
        foreach ($btklDetails as $btkl) {
            $kapasitas = $btkl->kapasitas_per_jam ?? 1;
            $biayaPerUnit = $kapasitas > 0 ? $btkl->tarif_per_jam / $kapasitas : 0;
            $totalBiayaPerUnit += $biayaPerUnit;
            
            echo "  - {$btkl->nama_proses}: Rp " . number_format($biayaPerUnit, 2, ',', '.') . "/unit" . PHP_EOL;
        }
        
        echo "  Total BTKL per unit: Rp " . number_format($totalBiayaPerUnit, 2, ',', '.') . PHP_EOL;
        echo "  Total BTKL di BomJobCosting: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    } else {
        echo "  - Tidak ada BomJobCosting" . PHP_EOL;
    }
    echo PHP_EOL;
}

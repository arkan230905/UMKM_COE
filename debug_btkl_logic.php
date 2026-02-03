<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG LOGIC BTKL YANG BENAR ===\n";

$produk = \App\Models\Produk::find(1);
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();

if ($bomJobCosting) {
    // Get BTKL data dengan join ke proses_produksis (sama seperti di BomController)
    $bomJobBtkl = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
        ->join('proses_produksis', 'bom_job_btkl.proses_produksi_id', '=', 'proses_produksis.id')
        ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
        ->select('bom_job_btkl.*', 'proses_produksis.tarif_btkl as tarif_per_jam', 'proses_produksis.kapasitas_per_jam')
        ->get();
    
    echo "--- BTKL DATA DENGAN JOIN ---\n";
    foreach ($bomJobBtkl as $item) {
        echo "Proses ID: " . $item->proses_produksi_id . "\n";
        echo "Durasi Jam: " . $item->durasi_jam . "\n";
        echo "Tarif per Jam: Rp " . number_format($item->tarif_per_jam, 0, ',', '.') . "\n";
        echo "Kapasitas per Jam: " . $item->kapasitas_per_jam . "\n";
        
        $kapasitas = $item->kapasitas_per_jam ?? 1;
        $biayaPerItem = ($item->tarif_per_jam / $kapasitas) * $item->durasi_jam;
        echo "Biaya per Item: Rp " . number_format($biayaPerItem, 0, ',', '.') . "\n";
        echo "Formula: (Rp " . number_format($item->tarif_per_jam, 0, ',', '.') . " ÷ " . $kapasitas . ") × " . $item->durasi_jam . " = Rp " . number_format($biayaPerItem, 0, ',', '.') . "\n";
        echo "---\n";
    }
    
    // Calculate biaya per produk: tarif_per_jam ÷ kapasitas_per_jam
    $totalBTKL = $bomJobBtkl->sum(function($item) {
        $kapasitas = $item->kapasitas_per_jam ?? 1;
        return ($item->tarif_per_jam / $kapasitas) * $item->durasi_jam;
    });
    
    echo "\n--- HASIL PERHITUNGAN ---\n";
    echo "Total BTKL (Logic BomController): Rp " . number_format($totalBTKL, 0, ',', '.') . "\n";
    
    // Compare dengan logic lama
    $totalBTKLLama = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->sum('total_biaya');
    echo "Total BTKL (Logic Lama): Rp " . number_format($totalBTKLLama, 0, ',', '.') . "\n";
    echo "Selisih: Rp " . number_format($totalBTKL - $totalBTKLLama, 0, ',', '.') . "\n";
}

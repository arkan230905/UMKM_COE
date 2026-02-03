<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECK BTKL DETAILS ===\n";

$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 1)->first();

if ($bomJobCosting) {
    $btklDetails = \App\Models\BomJobBtkl::where('bom_job_costing_id', $bomJobCosting->id)->get();
    
    foreach ($btklDetails as $detail) {
        echo "BTKL Detail ID: " . $detail->id . "\n";
        echo "Proses: " . ($detail->prosesProduksi->nama_proses ?? 'N/A') . "\n";
        echo "Jumlah Tenaga: " . $detail->jumlah_tenaga . "\n";
        echo "Tarif Per Jam: Rp " . number_format($detail->tarif_per_jam, 0, ',', '.') . "\n";
        echo "Waktu (Jam): " . $detail->waktu_jam . "\n";
        echo "Total Biaya: Rp " . number_format($detail->total_biaya, 0, ',', '.') . "\n";
        echo "---\n";
    }
}

echo "\n=== CHECK BOP DETAILS ===\n";

if ($bomJobCosting) {
    $bopDetails = \App\Models\BomJobBop::where('bom_job_costing_id', $bomJobCosting->id)->get();
    
    foreach ($bopDetails as $detail) {
        echo "BOP Detail ID: " . $detail->id . "\n";
        echo "Komponen: " . ($detail->komponenBop->nama_komponen ?? 'N/A') . "\n";
        echo "Biaya: Rp " . number_format($detail->biaya, 0, ',', '.') . "\n";
        echo "---\n";
    }
}

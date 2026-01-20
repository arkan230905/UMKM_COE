<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING BOP ACCESSOR ===\n\n";

// Test dengan data yang ada
$bomProsesBops = \App\Models\BomProsesBop::with(['komponenBop', 'bop', 'bomProses.prosesProduksi'])
    ->take(5)
    ->get();

if ($bomProsesBops->count() > 0) {
    foreach ($bomProsesBops as $bpb) {
        echo "ID: {$bpb->id}\n";
        echo "  - komponen_bop_id: " . ($bpb->komponen_bop_id ?? 'NULL') . "\n";
        echo "  - bop_id: " . ($bpb->bop_id ?? 'NULL') . "\n";
        echo "  - Nama BOP (accessor): {$bpb->nama_bop}\n";
        echo "  - Proses: " . ($bpb->bomProses->prosesProduksi->nama_proses ?? 'N/A') . "\n";
        echo "  - Kuantitas: {$bpb->kuantitas} × Rp " . number_format($bpb->tarif, 0, ',', '.') . " = Rp " . number_format($bpb->total_biaya, 0, ',', '.') . "\n";
        echo "\n";
    }
} else {
    echo "No data found\n";
}

echo "=== DONE ===\n";

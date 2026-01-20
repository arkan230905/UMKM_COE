<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING BOP DETAIL DATA ===\n\n";

$bomProsesBops = \App\Models\BomProsesBop::with(['bop', 'komponenBop', 'bomProses.prosesProduksi'])
    ->take(10)
    ->get();

foreach ($bomProsesBops as $bpb) {
    echo "ID: {$bpb->id}\n";
    echo "  Nama BOP: {$bpb->nama_bop}\n";
    echo "  Proses: " . ($bpb->bomProses->prosesProduksi->nama_proses ?? 'N/A') . "\n";
    echo "  kuantitas: {$bpb->kuantitas}\n";
    echo "  tarif: Rp " . number_format($bpb->tarif, 0, ',', '.') . "\n";
    echo "  total_biaya: Rp " . number_format($bpb->total_biaya, 0, ',', '.') . "\n";
    echo "  ---\n";
}

echo "\n=== CHECKING BomProses biaya_bop ===\n\n";
$bomProses = \App\Models\BomProses::with('prosesProduksi')->take(5)->get();
foreach ($bomProses as $bp) {
    echo "ID: {$bp->id} | Proses: " . ($bp->prosesProduksi->nama_proses ?? 'N/A') . " | biaya_bop: Rp " . number_format($bp->biaya_bop, 0, ',', '.') . "\n";
}

echo "\n=== DONE ===\n";

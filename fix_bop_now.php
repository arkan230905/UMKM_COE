<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Update Pengukusan
DB::table('produksi_proses')
    ->where('produksi_id', 3)
    ->where('nama_proses', 'LIKE', '%Pengukusan%')
    ->update([
        'biaya_bop' => 11400,
        'total_biaya_proses' => DB::raw('biaya_btkl + 11400')
    ]);

// Update Pengemasan
DB::table('produksi_proses')
    ->where('produksi_id', 3)
    ->where('nama_proses', 'LIKE', '%Pengemasan%')
    ->update([
        'biaya_bop' => 279240,
        'total_biaya_proses' => DB::raw('biaya_btkl + 279240')
    ]);

echo "BOP updated successfully!\n";

// Show results
$proses = DB::table('produksi_proses')->where('produksi_id', 3)->get();
foreach($proses as $p) {
    echo "\n{$p->nama_proses}:\n";
    echo "  BTKL: Rp " . number_format($p->biaya_btkl, 0, ',', '.') . "\n";
    echo "  BOP: Rp " . number_format($p->biaya_bop, 0, ',', '.') . "\n";
    echo "  Total: Rp " . number_format($p->total_biaya_proses, 0, ',', '.') . "\n";
}

<?php
/**
 * Script untuk mengecek hasil import COA
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Cek Data COA ===\n\n";

// Total data
$total = DB::table('coas')->where('company_id', 1)->count();
echo "Total akun COA: $total\n\n";

// Grouping by tipe_akun
echo "Breakdown per Tipe Akun:\n";
$breakdown = DB::table('coas')
    ->select('tipe_akun', DB::raw('COUNT(*) as jumlah'))
    ->where('company_id', 1)
    ->groupBy('tipe_akun')
    ->orderBy('tipe_akun')
    ->get();

foreach ($breakdown as $item) {
    echo "  - {$item->tipe_akun}: {$item->jumlah} akun\n";
}

echo "\n";

// Cek kode akun yang ada
echo "Daftar semua kode akun:\n";
$allCodes = DB::table('coas')
    ->where('company_id', 1)
    ->orderBy('kode_akun')
    ->get(['kode_akun', 'nama_akun']);

foreach ($allCodes as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
}

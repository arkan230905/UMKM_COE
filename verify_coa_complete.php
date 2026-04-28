<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Verifikasi Data COA Lengkap ===\n\n";

$total = DB::table('coas')->where('company_id', 1)->count();
echo "✓ Total: $total akun COA\n\n";

// Tampilkan akun header
echo "AKUN HEADER (is_akun_header = 1):\n";
$headers = DB::table('coas')
    ->where('company_id', 1)
    ->where('is_akun_header', 1)
    ->orderBy('kode_akun')
    ->get(['kode_akun', 'nama_akun', 'tipe_akun']);

foreach ($headers as $h) {
    echo "  {$h->kode_akun} - {$h->nama_akun} ({$h->tipe_akun})\n";
}

echo "\n";

// Tampilkan beberapa akun detail per kategori
echo "CONTOH AKUN ASET:\n";
$aset = DB::table('coas')
    ->where('company_id', 1)
    ->where('tipe_akun', 'Aset')
    ->orderBy('kode_akun')
    ->limit(10)
    ->get(['kode_akun', 'nama_akun', 'saldo_awal']);

foreach ($aset as $a) {
    $saldo = number_format($a->saldo_awal, 0, ',', '.');
    echo "  {$a->kode_akun} - {$a->nama_akun} (Saldo Awal: Rp $saldo)\n";
}

echo "\n";

echo "CONTOH AKUN BIAYA PRODUKSI:\n";
$biaya = DB::table('coas')
    ->where('company_id', 1)
    ->whereIn('tipe_akun', ['Biaya Bahan Baku', 'Biaya Tenaga Kerja Langsung', 'Biaya Overhead Pabrik'])
    ->orderBy('kode_akun')
    ->limit(10)
    ->get(['kode_akun', 'nama_akun', 'tipe_akun']);

foreach ($biaya as $b) {
    echo "  {$b->kode_akun} - {$b->nama_akun} ({$b->tipe_akun})\n";
}

echo "\n✓ Verifikasi selesai!\n";

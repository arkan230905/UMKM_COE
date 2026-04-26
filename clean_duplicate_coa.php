<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Membersihkan Duplikat COA ===\n\n";

// Cek total sebelum
$totalBefore = DB::table('coas')->where('company_id', 1)->count();
echo "Total akun sebelum: $totalBefore\n\n";

// Cari duplikat berdasarkan kode_akun
$duplicates = DB::table('coas')
    ->select('kode_akun', DB::raw('COUNT(*) as jumlah'), DB::raw('MIN(id) as keep_id'))
    ->where('company_id', 1)
    ->groupBy('kode_akun')
    ->having('jumlah', '>', 1)
    ->get();

echo "Ditemukan " . $duplicates->count() . " kode akun yang duplikat:\n";

$deleted = 0;

foreach ($duplicates as $dup) {
    echo "  Kode {$dup->kode_akun}: {$dup->jumlah} duplikat";
    
    // Hapus semua kecuali yang pertama (ID terkecil)
    $deletedCount = DB::table('coas')
        ->where('company_id', 1)
        ->where('kode_akun', $dup->kode_akun)
        ->where('id', '!=', $dup->keep_id)
        ->delete();
    
    $deleted += $deletedCount;
    echo " → Dihapus: $deletedCount\n";
}

echo "\n";

// Cek total setelah
$totalAfter = DB::table('coas')->where('company_id', 1)->count();
echo "Total akun setelah: $totalAfter\n";
echo "Total dihapus: $deleted\n\n";

// Verifikasi tidak ada duplikat lagi
$stillDuplicate = DB::table('coas')
    ->select('kode_akun', DB::raw('COUNT(*) as jumlah'))
    ->where('company_id', 1)
    ->groupBy('kode_akun')
    ->having('jumlah', '>', 1)
    ->count();

if ($stillDuplicate == 0) {
    echo "✓ Tidak ada duplikat lagi!\n";
} else {
    echo "✗ Masih ada $stillDuplicate duplikat!\n";
}

// Tampilkan breakdown
echo "\nBreakdown per Tipe Akun:\n";
$breakdown = DB::table('coas')
    ->select('tipe_akun', DB::raw('COUNT(*) as jumlah'))
    ->where('company_id', 1)
    ->groupBy('tipe_akun')
    ->orderBy('tipe_akun')
    ->get();

foreach ($breakdown as $item) {
    echo "  - {$item->tipe_akun}: {$item->jumlah} akun\n";
}

echo "\n✓ Pembersihan selesai!\n";

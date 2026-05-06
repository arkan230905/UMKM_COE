<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$userId = 4; // Muhammad Arkan Abiyyu
echo "=== FINAL AUDIT & FIX ALL USER_ID ===\n\n";

$tables = [
    'bahan_bakus'       => 'user_id',
    'bahan_pendukungs'  => 'user_id',
    'produks'           => 'user_id',
    'pegawais'          => 'user_id',
    'vendors'           => 'user_id',
    'coas'              => 'user_id',
    'jabatans'          => 'user_id',
    'pembelians'        => 'user_id',
    'penjualans'        => 'user_id',
    'penggajians'       => 'user_id',
    'produksis'         => 'user_id',
    'presensis'         => 'user_id',
    'pelunasan_utangs'  => 'user_id',
    'pembayaran_beban'  => 'user_id',
    'journal_entries'   => 'user_id',
    'jurnal_umum'       => 'user_id',
    'asets'             => 'user_id',
    'bops'              => 'user_id',
];

$totalFixed = 0;
foreach ($tables as $table => $col) {
    try {
        // Check if column exists
        $cols = DB::select("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'");
        if (empty($cols)) {
            echo "  SKIP {$table} - no {$col} column\n";
            continue;
        }
        
        $nullCount = DB::table($table)->whereNull($col)->count();
        if ($nullCount > 0) {
            DB::table($table)->whereNull($col)->update([$col => $userId]);
            echo "  FIXED {$table}: {$nullCount} records → user_id={$userId}\n";
            $totalFixed += $nullCount;
        } else {
            $total = DB::table($table)->where($col, $userId)->count();
            echo "  OK    {$table}: {$total} records with user_id={$userId}\n";
        }
    } catch (\Exception $e) {
        echo "  ERROR {$table}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== TOTAL FIXED: {$totalFixed} records ===\n";

// Special fix: journal_entries from pembelian
$fixed = DB::statement("
    UPDATE journal_entries je
    JOIN pembelians p ON je.ref_id = p.id AND je.ref_type = 'purchase'
    SET je.user_id = p.user_id
    WHERE je.user_id IS NULL OR je.user_id != p.user_id
");
echo "\nFixed journal_entries from pembelian: done\n";

// Special fix: jurnal_umum from pembelian
$fixed = DB::statement("
    UPDATE jurnal_umum ju
    JOIN pembelians p ON ju.referensi = p.nomor_pembelian AND ju.tipe_referensi = 'pembelian'
    SET ju.user_id = p.user_id
    WHERE ju.user_id IS NULL OR ju.user_id != p.user_id
");
echo "Fixed jurnal_umum from pembelian: done\n";

echo "\n=== SEMUA SELESAI ===\n";
echo "Refresh halaman untuk melihat data yang sudah diperbaiki.\n";

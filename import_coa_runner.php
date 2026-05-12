<?php
/**
 * Script untuk mengimport data COA ke database
 * Jalankan dengan: php import_coa_runner.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Import Data COA ===\n";
echo "Database: " . env('DB_DATABASE') . "\n";
echo "Host: " . env('DB_HOST') . "\n\n";

try {
    // Baca file SQL
    $sqlFile = __DIR__ . '/import_coa_data.sql';
    
    if (!file_exists($sqlFile)) {
        die("Error: File import_coa_data.sql tidak ditemukan!\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    echo "Memulai import data COA...\n";
    
    // Jalankan SQL
    DB::unprepared($sql);
    
    echo "✓ Import berhasil!\n\n";
    
    // Cek jumlah data
    $count = DB::table('coas')->where('company_id', 1)->count();
    echo "Total akun COA di database: $count\n";
    
    // Tampilkan beberapa contoh data
    echo "\nContoh data COA yang berhasil diimport:\n";
    $samples = DB::table('coas')
        ->where('company_id', 1)
        ->orderBy('kode_akun')
        ->limit(10)
        ->get(['kode_akun', 'nama_akun', 'tipe_akun']);
    
    foreach ($samples as $coa) {
        echo "  - {$coa->kode_akun}: {$coa->nama_akun} ({$coa->tipe_akun})\n";
    }
    
    echo "\n✓ Selesai!\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

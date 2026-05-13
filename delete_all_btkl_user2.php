<?php

/**
 * Script untuk menghapus semua data BTKL milik user_id = 2
 * Jalankan: php delete_all_btkl_user2.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DELETE ALL BTKL DATA FOR USER ID 2 ===\n\n";

$userId = 2;

// Count existing data
$countProses = DB::table('proses_produksis')->where('user_id', $userId)->count();
$countBtkl = DB::table('btkls')->where('user_id', $userId)->count();
$countBopProses = DB::table('bop_proses')->where('user_id', $userId)->count();

echo "Data yang akan dihapus:\n";
echo "- Proses Produksi: $countProses records\n";
echo "- BTKL: $countBtkl records\n";
echo "- BOP Proses: $countBopProses records\n\n";

if ($countProses == 0 && $countBtkl == 0 && $countBopProses == 0) {
    echo "✓ Tidak ada data untuk dihapus\n";
    exit;
}

echo "Menghapus data...\n\n";

try {
    DB::beginTransaction();
    
    // Delete BOP Proses first (foreign key constraint)
    $deletedBop = DB::table('bop_proses')->where('user_id', $userId)->delete();
    echo "✓ Deleted $deletedBop BOP Proses records\n";
    
    // Delete Proses Produksi
    $deletedProses = DB::table('proses_produksis')->where('user_id', $userId)->delete();
    echo "✓ Deleted $deletedProses Proses Produksi records\n";
    
    // Delete BTKL
    $deletedBtkl = DB::table('btkls')->where('user_id', $userId)->delete();
    echo "✓ Deleted $deletedBtkl BTKL records\n";
    
    DB::commit();
    
    echo "\n=== SUCCESS ===\n";
    echo "Semua data BTKL untuk user_id = $userId berhasil dihapus!\n";
    echo "Silakan coba input data baru.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
}

<?php

/**
 * Script untuk memperbaiki jurnal umum menggunakan Laravel
 * Jalankan dengan: php fix_jurnal_laravel.php
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PERBAIKAN JURNAL UMUM APRIL 2026 ===\n\n";

try {
    // Test koneksi database
    DB::connection()->getPdo();
    echo "✓ Koneksi database berhasil\n\n";
    
    // 1. Lihat data jurnal saat ini
    echo "1. DATA JURNAL SAAT INI:\n";
    $currentJournals = DB::table('jurnal_umum')
        ->where('tanggal', '2026-04-30')
        ->where('keterangan', 'like', '%Penyusutan%')
        ->orderBy('debit', 'desc')
        ->get();
    
    foreach ($currentJournals as $journal) {
        echo "  ID: {$journal->id} - {$journal->keterangan}\n";
        echo "    Debit: Rp " . number_format($journal->debit, 0, ',', '.') . "\n";
        echo "    Kredit: Rp " . number_format($journal->kredit, 0, ',', '.') . "\n\n";
    }
    
    if ($currentJournals->isEmpty()) {
        echo "  Tidak ada jurnal penyusutan ditemukan untuk tanggal 2026-04-30\n";
        exit;
    }
    
    // 2. Mulai transaksi
    DB::beginTransaction();
    
    echo "2. MEMULAI PERBAIKAN...\n\n";
    
    // Data koreksi
    $corrections = [
        ['old' => 1416667.00, 'new' => 1333333.00, 'asset' => 'Mesin', 'keyword' => '%Mesin%'],
        ['old' => 2833333.00, 'new' => 659474.00, 'asset' => 'Peralatan', 'keyword' => '%Peralatan%'],
        ['old' => 2361111.00, 'new' => 888889.00, 'asset' => 'Kendaraan', 'keyword' => '%Kendaraan%']
    ];
    
    $totalUpdated = 0;
    
    foreach ($corrections as $correction) {
        echo "Memperbaiki {$correction['asset']}:\n";
        
        // Update debit
        $updated1 = DB::table('jurnal_umum')
            ->where('tanggal', '2026-04-30')
            ->where('keterangan', 'like', '%Penyusutan%')
            ->where('keterangan', 'like', $correction['keyword'])
            ->where('debit', $correction['old'])
            ->update(['debit' => $correction['new']]);
        
        // Update kredit
        $updated2 = DB::table('jurnal_umum')
            ->where('tanggal', '2026-04-30')
            ->where('keterangan', 'like', '%Penyusutan%')
            ->where('keterangan', 'like', $correction['keyword'])
            ->where('kredit', $correction['old'])
            ->update(['kredit' => $correction['new']]);
        
        echo "  Debit updated: {$updated1} rows\n";
        echo "  Kredit updated: {$updated2} rows\n";
        echo "  Rp " . number_format($correction['old'], 0, ',', '.') . " → Rp " . number_format($correction['new'], 0, ',', '.') . "\n\n";
        
        $totalUpdated += $updated1 + $updated2;
    }
    
    // 3. Validasi hasil
    echo "3. VALIDASI HASIL:\n";
    $updatedJournals = DB::table('jurnal_umum')
        ->where('tanggal', '2026-04-30')
        ->where('keterangan', 'like', '%Penyusutan%')
        ->orderBy('debit', 'desc')
        ->get();
    
    $success = true;
    $expectedValues = [1333333, 659474, 888889];
    
    foreach ($updatedJournals as $journal) {
        echo "  {$journal->keterangan}\n";
        echo "    Debit: Rp " . number_format($journal->debit, 0, ',', '.') . "\n";
        echo "    Kredit: Rp " . number_format($journal->kredit, 0, ',', '.') . "\n";
        
        // Cek apakah nilai sudah benar
        $amount = max($journal->debit, $journal->kredit);
        if (in_array($amount, $expectedValues)) {
            echo "    ✓ BENAR\n";
        } else {
            echo "    ✗ MASIH SALAH (nilai: {$amount})\n";
            $success = false;
        }
        echo "\n";
    }
    
    if ($success && $totalUpdated > 0) {
        // Commit transaksi
        DB::commit();
        echo "✓ PERBAIKAN BERHASIL! Total {$totalUpdated} baris diupdate.\n";
        
        // Update data aset juga
        echo "\n4. UPDATE DATA ASET...\n";
        
        $assetUpdates = [
            ['amount' => 1333333, 'yearly' => 16000000, 'keyword' => '%Mesin%'],
            ['amount' => 659474, 'yearly' => 7913688, 'keyword' => '%Peralatan%'],
            ['amount' => 888889, 'yearly' => 10666668, 'keyword' => '%Kendaraan%']
        ];
        
        foreach ($assetUpdates as $update) {
            $updated = DB::table('asets')
                ->where('nama_aset', 'like', $update['keyword'])
                ->update([
                    'penyusutan_per_bulan' => $update['amount'],
                    'penyusutan_per_tahun' => $update['yearly']
                ]);
            
            $keyword = str_replace('%', '', $update['keyword']);
            echo "  {$keyword}: {$updated} aset diupdate\n";
        }
        
        echo "\n✓ SEMUA PERBAIKAN SELESAI!\n";
        
    } else {
        // Rollback jika ada masalah
        DB::rollback();
        echo "✗ PERBAIKAN GAGAL! Transaksi dibatalkan.\n";
        
        if ($totalUpdated == 0) {
            echo "\nKemungkinan penyebab:\n";
            echo "1. Nilai yang dicari tidak ditemukan\n";
            echo "2. Format tanggal berbeda\n";
            echo "3. Keterangan jurnal tidak sesuai pattern\n";
        }
    }
    
} catch (Exception $e) {
    if (DB::transactionLevel() > 0) {
        DB::rollback();
    }
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== SELESAI ===\n";
echo "Silakan cek kembali jurnal umum di aplikasi.\n";
echo "Jika masih belum berubah, coba:\n";
echo "1. Clear cache: php artisan cache:clear\n";
echo "2. Clear config: php artisan config:clear\n";
echo "3. Restart web server\n";
?>
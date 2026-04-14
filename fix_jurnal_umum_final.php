<?php

/**
 * Script final untuk memperbaiki jurnal_umum April 2026
 * Jalankan dengan: php fix_jurnal_umum_final.php
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    use Illuminate\Support\Facades\DB;
    
    echo "=== MEMPERBAIKI JURNAL UMUM APRIL 2026 ===\n\n";
    
    // Cek data saat ini
    echo "1. DATA SAAT INI:\n";
    $currentData = DB::table('jurnal_umum')
        ->where('tanggal', '2026-04-30')
        ->where('keterangan', 'like', '%Penyusutan%')
        ->orderBy('debit', 'desc')
        ->get();
    
    foreach ($currentData as $row) {
        $amount = max($row->debit, $row->kredit);
        echo "  ID: {$row->id} - Amount: Rp " . number_format($amount, 0, ',', '.') . "\n";
        echo "  Keterangan: {$row->keterangan}\n\n";
    }
    
    if ($currentData->isEmpty()) {
        echo "  Tidak ada data jurnal penyusutan ditemukan!\n";
        exit;
    }
    
    // Mulai transaksi
    DB::beginTransaction();
    
    echo "2. MEMULAI PERBAIKAN...\n\n";
    
    // Update Mesin: 1416667 -> 1333333
    $updated1 = DB::table('jurnal_umum')
        ->where('tanggal', '2026-04-30')
        ->where('keterangan', 'like', '%Mesin%')
        ->where('debit', 1416667)
        ->update(['debit' => 1333333]);
    
    $updated2 = DB::table('jurnal_umum')
        ->where('tanggal', '2026-04-30')
        ->where('keterangan', 'like', '%Mesin%')
        ->where('kredit', 1416667)
        ->update(['kredit' => 1333333]);
    
    echo "Mesin - Debit: {$updated1}, Kredit: {$updated2}\n";
    
    // Update Peralatan: 2833333 -> 659474
    $updated3 = DB::table('jurnal_umum')
        ->where('tanggal', '2026-04-30')
        ->where('keterangan', 'like', '%Peralatan%')
        ->where('debit', 2833333)
        ->update(['debit' => 659474]);
    
    $updated4 = DB::table('jurnal_umum')
        ->where('tanggal', '2026-04-30')
        ->where('keterangan', 'like', '%Peralatan%')
        ->where('kredit', 2833333)
        ->update(['kredit' => 659474]);
    
    echo "Peralatan - Debit: {$updated3}, Kredit: {$updated4}\n";
    
    // Update Kendaraan: 2361111 -> 888889
    $updated5 = DB::table('jurnal_umum')
        ->where('tanggal', '2026-04-30')
        ->where('keterangan', 'like', '%Kendaraan%')
        ->where('debit', 2361111)
        ->update(['debit' => 888889]);
    
    $updated6 = DB::table('jurnal_umum')
        ->where('tanggal', '2026-04-30')
        ->where('keterangan', 'like', '%Kendaraan%')
        ->where('kredit', 2361111)
        ->update(['kredit' => 888889]);
    
    echo "Kendaraan - Debit: {$updated5}, Kredit: {$updated6}\n\n";
    
    $totalUpdated = $updated1 + $updated2 + $updated3 + $updated4 + $updated5 + $updated6;
    
    if ($totalUpdated > 0) {
        DB::commit();
        echo "✓ BERHASIL! Total {$totalUpdated} baris diupdate.\n\n";
        
        // Validasi hasil
        echo "3. HASIL SETELAH UPDATE:\n";
        $newData = DB::table('jurnal_umum')
            ->where('tanggal', '2026-04-30')
            ->where('keterangan', 'like', '%Penyusutan%')
            ->orderBy('debit', 'desc')
            ->get();
        
        foreach ($newData as $row) {
            $amount = max($row->debit, $row->kredit);
            $type = $row->debit > 0 ? 'Debit' : 'Kredit';
            echo "  {$type}: Rp " . number_format($amount, 0, ',', '.') . "\n";
            echo "  Keterangan: {$row->keterangan}\n\n";
        }
        
    } else {
        DB::rollback();
        echo "✗ Tidak ada data yang diupdate.\n";
    }
    
} catch (Exception $e) {
    if (isset($app)) {
        DB::rollback();
    }
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== SELESAI ===\n";
echo "Silakan refresh halaman jurnal umum di browser.\n";
?>
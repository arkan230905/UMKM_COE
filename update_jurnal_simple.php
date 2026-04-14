<?php

/**
 * Script sederhana untuk update jurnal umum
 * Jalankan dengan: php update_jurnal_simple.php
 */

// Coba bootstrap Laravel
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    
    if (file_exists(__DIR__ . '/bootstrap/app.php')) {
        $app = require_once __DIR__ . '/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        use Illuminate\Support\Facades\DB;
        
        echo "=== UPDATE JURNAL UMUM APRIL 2026 ===\n\n";
        
        try {
            // Test koneksi
            DB::connection()->getPdo();
            echo "✓ Koneksi database berhasil\n\n";
            
            // Lihat data saat ini
            echo "Data jurnal saat ini:\n";
            $journals = DB::select("
                SELECT id, keterangan, debit, kredit 
                FROM jurnal_umum 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Penyusutan%'
                ORDER BY debit DESC
            ");
            
            foreach ($journals as $journal) {
                echo "ID: {$journal->id} - Debit: {$journal->debit} - Kredit: {$journal->kredit}\n";
                echo "Keterangan: {$journal->keterangan}\n\n";
            }
            
            if (empty($journals)) {
                echo "Tidak ada jurnal penyusutan ditemukan.\n";
                exit;
            }
            
            echo "Memulai update...\n\n";
            
            // Update Mesin: 1416667 -> 1333333
            $updated1 = DB::update("
                UPDATE jurnal_umum 
                SET debit = 1333333 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Mesin%' 
                  AND debit = 1416667
            ");
            
            $updated2 = DB::update("
                UPDATE jurnal_umum 
                SET kredit = 1333333 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Mesin%' 
                  AND kredit = 1416667
            ");
            
            echo "Mesin - Debit updated: {$updated1}, Kredit updated: {$updated2}\n";
            
            // Update Peralatan: 2833333 -> 659474
            $updated3 = DB::update("
                UPDATE jurnal_umum 
                SET debit = 659474 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Peralatan%' 
                  AND debit = 2833333
            ");
            
            $updated4 = DB::update("
                UPDATE jurnal_umum 
                SET kredit = 659474 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Peralatan%' 
                  AND kredit = 2833333
            ");
            
            echo "Peralatan - Debit updated: {$updated3}, Kredit updated: {$updated4}\n";
            
            // Update Kendaraan: 2361111 -> 888889
            $updated5 = DB::update("
                UPDATE jurnal_umum 
                SET debit = 888889 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Kendaraan%' 
                  AND debit = 2361111
            ");
            
            $updated6 = DB::update("
                UPDATE jurnal_umum 
                SET kredit = 888889 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Kendaraan%' 
                  AND kredit = 2361111
            ");
            
            echo "Kendaraan - Debit updated: {$updated5}, Kredit updated: {$updated6}\n\n";
            
            $totalUpdated = $updated1 + $updated2 + $updated3 + $updated4 + $updated5 + $updated6;
            
            if ($totalUpdated > 0) {
                echo "✓ Total {$totalUpdated} baris berhasil diupdate!\n\n";
                
                // Lihat hasil
                echo "Data jurnal setelah update:\n";
                $newJournals = DB::select("
                    SELECT keterangan, debit, kredit 
                    FROM jurnal_umum 
                    WHERE tanggal = '2026-04-30' 
                      AND keterangan LIKE '%Penyusutan%'
                    ORDER BY debit DESC
                ");
                
                foreach ($newJournals as $journal) {
                    echo "Debit: {$journal->debit} - Kredit: {$journal->kredit}\n";
                    echo "Keterangan: {$journal->keterangan}\n\n";
                }
                
            } else {
                echo "✗ Tidak ada data yang diupdate. Kemungkinan nilai sudah benar atau tidak ditemukan.\n";
            }
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "File bootstrap/app.php tidak ditemukan.\n";
    }
} else {
    echo "File vendor/autoload.php tidak ditemukan.\n";
    echo "Pastikan Anda menjalankan script ini dari root directory Laravel.\n";
}

echo "\n=== SELESAI ===\n";
?>
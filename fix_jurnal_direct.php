<?php

/**
 * Script langsung untuk memperbaiki jurnal umum April 2026
 * Jalankan dengan: php fix_jurnal_direct.php
 */

// Konfigurasi database - sesuaikan dengan .env Anda
$host = 'localhost';
$dbname = 'your_database_name'; // Ganti dengan nama database Anda
$username = 'your_username';    // Ganti dengan username database Anda
$password = 'your_password';    // Ganti dengan password database Anda

try {
    // Koneksi ke database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== PERBAIKAN JURNAL UMUM APRIL 2026 ===\n\n";
    
    // 1. Lihat data jurnal saat ini
    echo "1. DATA JURNAL SAAT INI:\n";
    $stmt = $pdo->query("
        SELECT id, keterangan, debit, kredit 
        FROM jurnal_umum 
        WHERE tanggal = '2026-04-30' 
          AND keterangan LIKE '%Penyusutan%'
        ORDER BY debit DESC
    ");
    
    $currentJournals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($currentJournals as $journal) {
        echo "  ID: {$journal['id']} - {$journal['keterangan']}\n";
        echo "    Debit: Rp " . number_format($journal['debit'], 0, ',', '.') . "\n";
        echo "    Kredit: Rp " . number_format($journal['kredit'], 0, ',', '.') . "\n\n";
    }
    
    // 2. Mulai transaksi
    $pdo->beginTransaction();
    
    echo "2. MEMULAI PERBAIKAN...\n\n";
    
    // Data koreksi
    $corrections = [
        ['old' => 1416667, 'new' => 1333333, 'asset' => 'Mesin'],
        ['old' => 2833333, 'new' => 659474, 'asset' => 'Peralatan'],
        ['old' => 2361111, 'new' => 888889, 'asset' => 'Kendaraan']
    ];
    
    $totalUpdated = 0;
    
    foreach ($corrections as $correction) {
        echo "Memperbaiki {$correction['asset']}:\n";
        
        // Update debit
        $stmt = $pdo->prepare("
            UPDATE jurnal_umum 
            SET debit = ? 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE ? 
              AND debit = ?
        ");
        $result1 = $stmt->execute([$correction['new'], "%{$correction['asset']}%", $correction['old']]);
        $updated1 = $stmt->rowCount();
        
        // Update kredit
        $stmt = $pdo->prepare("
            UPDATE jurnal_umum 
            SET kredit = ? 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE ? 
              AND kredit = ?
        ");
        $result2 = $stmt->execute([$correction['new'], "%{$correction['asset']}%", $correction['old']]);
        $updated2 = $stmt->rowCount();
        
        echo "  Debit updated: {$updated1} rows\n";
        echo "  Kredit updated: {$updated2} rows\n";
        echo "  Rp " . number_format($correction['old'], 0, ',', '.') . " → Rp " . number_format($correction['new'], 0, ',', '.') . "\n\n";
        
        $totalUpdated += $updated1 + $updated2;
    }
    
    // 3. Validasi hasil
    echo "3. VALIDASI HASIL:\n";
    $stmt = $pdo->query("
        SELECT keterangan, debit, kredit 
        FROM jurnal_umum 
        WHERE tanggal = '2026-04-30' 
          AND keterangan LIKE '%Penyusutan%'
        ORDER BY debit DESC
    ");
    
    $updatedJournals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $success = true;
    
    foreach ($updatedJournals as $journal) {
        echo "  {$journal['keterangan']}\n";
        echo "    Debit: Rp " . number_format($journal['debit'], 0, ',', '.') . "\n";
        echo "    Kredit: Rp " . number_format($journal['kredit'], 0, ',', '.') . "\n";
        
        // Cek apakah nilai sudah benar
        $amount = max($journal['debit'], $journal['kredit']);
        if (in_array($amount, [1333333, 659474, 888889])) {
            echo "    ✓ BENAR\n";
        } else {
            echo "    ✗ MASIH SALAH\n";
            $success = false;
        }
        echo "\n";
    }
    
    if ($success && $totalUpdated > 0) {
        // Commit transaksi
        $pdo->commit();
        echo "✓ PERBAIKAN BERHASIL! Total {$totalUpdated} baris diupdate.\n";
        
        // Update data aset juga
        echo "\n4. UPDATE DATA ASET...\n";
        
        $assetUpdates = [
            ['amount' => 1333333, 'keyword' => 'Mesin'],
            ['amount' => 659474, 'keyword' => 'Peralatan'],
            ['amount' => 888889, 'keyword' => 'Kendaraan']
        ];
        
        foreach ($assetUpdates as $update) {
            $stmt = $pdo->prepare("
                UPDATE asets 
                SET penyusutan_per_bulan = ?,
                    penyusutan_per_tahun = ?
                WHERE nama_aset LIKE ?
            ");
            $result = $stmt->execute([
                $update['amount'], 
                $update['amount'] * 12, 
                "%{$update['keyword']}%"
            ]);
            $updated = $stmt->rowCount();
            
            echo "  {$update['keyword']}: {$updated} aset diupdate\n";
        }
        
    } else {
        // Rollback jika ada masalah
        $pdo->rollback();
        echo "✗ PERBAIKAN GAGAL! Transaksi dibatalkan.\n";
    }
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nPastikan:\n";
    echo "1. Konfigurasi database sudah benar\n";
    echo "2. Database server berjalan\n";
    echo "3. User memiliki permission UPDATE\n";
}

echo "\n=== SELESAI ===\n";
echo "Silakan cek kembali jurnal umum di aplikasi.\n";
echo "Jika masih belum berubah, mungkin ada cache yang perlu di-clear.\n";
?>
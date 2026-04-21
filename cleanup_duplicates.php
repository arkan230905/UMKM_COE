<?php
/**
 * Script untuk membersihkan duplikasi journal entries pembayaran beban
 * Berdasarkan data yang diberikan user:
 * - Hanya 2 data yang benar (551 BOP Sewa Tempat dan 550 BOP Listrik)
 * - Ada 4 data yang muncul (duplikasi)
 */

$host = '127.0.0.1';
$db = 'eadt_umkm';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CLEANUP DUPLIKASI PEMBAYARAN BEBAN ===\n\n";
    
    // 1. Lihat semua entries pada 28-29 April
    echo "1. ENTRIES PADA 28-29 APRIL 2026:\n";
    $sql = "
    SELECT 
        je.id,
        je.entry_date,
        je.description,
        je.created_at,
        COUNT(jl.id) as line_count,
        SUM(jl.debit) as total_debit,
        SUM(jl.credit) as total_credit
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
    WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
    GROUP BY je.id
    ORDER BY je.entry_date, je.id
    ";
    
    $stmt = $pdo->query($sql);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($entries as $entry) {
        echo "  Entry " . $entry['id'] . " | " . $entry['entry_date'] . " | " . $entry['description'] . " | Debit: " . $entry['total_debit'] . " | Created: " . $entry['created_at'] . "\n";
    }
    
    echo "\nTotal entries: " . count($entries) . "\n\n";
    
    // 2. Cari duplikasi
    echo "2. MENCARI DUPLIKASI:\n";
    $sql = "
    SELECT 
        je1.id as entry1_id,
        je2.id as entry2_id,
        je1.entry_date,
        je1.description,
        je1.created_at as created1,
        je2.created_at as created2
    FROM journal_entries je1
    JOIN journal_entries je2 ON 
        DATE(je1.entry_date) = DATE(je2.entry_date) AND
        je1.description = je2.description AND
        je1.id < je2.id
    WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
    ";
    
    $stmt = $pdo->query($sql);
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "  Duplikasi ditemukan: " . count($duplicates) . "\n\n";
        
        $entriesToDelete = [];
        foreach ($duplicates as $dup) {
            echo "  Entry " . $dup['entry1_id'] . " dan Entry " . $dup['entry2_id'] . "\n";
            echo "    Tanggal: " . $dup['entry_date'] . "\n";
            echo "    Deskripsi: " . $dup['description'] . "\n";
            echo "    Created: " . $dup['created1'] . " vs " . $dup['created2'] . "\n";
            echo "    → HAPUS Entry " . $dup['entry2_id'] . " (yang lebih baru)\n\n";
            
            $entriesToDelete[] = $dup['entry2_id'];
        }
        
        // 3. Hapus duplikasi
        if (count($entriesToDelete) > 0) {
            echo "3. MENGHAPUS DUPLIKASI:\n";
            
            foreach ($entriesToDelete as $entryId) {
                // Hapus journal lines terlebih dahulu
                $sql = "DELETE FROM journal_lines WHERE journal_entry_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$entryId]);
                $linesDeleted = $stmt->rowCount();
                
                // Hapus journal entry
                $sql = "DELETE FROM journal_entries WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$entryId]);
                $entriesDeleted = $stmt->rowCount();
                
                echo "  Entry " . $entryId . ": Deleted " . $linesDeleted . " lines, " . $entriesDeleted . " entry\n";
            }
            
            echo "\n✓ Cleanup selesai!\n\n";
            
            // 4. Verifikasi hasil
            echo "4. VERIFIKASI HASIL:\n";
            $sql = "
            SELECT 
                je.id,
                je.entry_date,
                je.description,
                COUNT(jl.id) as line_count,
                SUM(jl.debit) as total_debit
            FROM journal_entries je
            LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
            WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
            GROUP BY je.id
            ORDER BY je.entry_date, je.id
            ";
            
            $stmt = $pdo->query($sql);
            $entriesAfter = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "  Total entries setelah cleanup: " . count($entriesAfter) . "\n";
            foreach ($entriesAfter as $entry) {
                echo "    Entry " . $entry['id'] . " | " . $entry['entry_date'] . " | " . $entry['description'] . " | Debit: " . $entry['total_debit'] . "\n";
            }
        }
    } else {
        echo "  Tidak ada duplikasi ditemukan.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

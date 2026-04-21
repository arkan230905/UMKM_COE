<?php
$host = '127.0.0.1';
$db = 'eadt_umkm';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIX DUPLIKASI PEMBAYARAN BEBAN ===\n\n";
    
    // 1. Lihat semua entries dengan detail lines
    echo "1. ENTRIES DENGAN DETAIL LINES:\n";
    $sql = "
    SELECT 
        je.id,
        je.entry_date,
        je.description,
        je.created_at,
        GROUP_CONCAT(CONCAT('Akun:', jl.account_id, ' Debit:', jl.debit, ' Kredit:', jl.credit) SEPARATOR ' | ') as lines_detail
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
    WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
    GROUP BY je.id
    ORDER BY je.entry_date, je.id
    ";
    
    $stmt = $pdo->query($sql);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $entriesToDelete = [];
    
    foreach ($entries as $entry) {
        echo "Entry " . $entry['id'] . " | " . $entry['entry_date'] . " | " . $entry['description'] . "\n";
        echo "  Lines: " . $entry['lines_detail'] . "\n";
        
        // Logika untuk menentukan entry mana yang harus dihapus
        // Jika ada 2 entries dengan tanggal dan deskripsi sama, hapus yang dibuat lebih belakangan
        
        // Untuk 28/04 - Pembayaran Beban Sewa
        if (strpos($entry['entry_date'], '2026-04-28') !== false && strpos($entry['description'], 'Sewa') !== false) {
            // Cek apakah ada duplikasi
            $checkSql = "
            SELECT COUNT(*) as cnt FROM journal_entries 
            WHERE DATE(entry_date) = '2026-04-28' 
            AND description LIKE '%Sewa%'
            ";
            $checkStmt = $pdo->query($checkSql);
            $count = $checkStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
            
            if ($count > 1) {
                // Ada duplikasi, cek apakah ini yang salah (akun 550 bukan 551)
                $checkLineSql = "
                SELECT COUNT(*) as cnt FROM journal_lines jl
                JOIN journal_entries je ON je.id = jl.journal_entry_id
                WHERE je.id = ? AND jl.account_id = 550
                ";
                $checkLineStmt = $pdo->prepare($checkLineSql);
                $checkLineStmt->execute([$entry['id']]);
                $hasWrongAccount = $checkLineStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
                
                if ($hasWrongAccount > 0) {
                    echo "  ⚠️ ENTRY INI SALAH (akun 550 bukan 551) - AKAN DIHAPUS\n";
                    $entriesToDelete[] = $entry['id'];
                }
            }
        }
        
        // Untuk 29/04 - Pembayaran Beban Listrik
        if (strpos($entry['entry_date'], '2026-04-29') !== false && strpos($entry['description'], 'Listrik') !== false) {
            // Cek apakah ada duplikasi
            $checkSql = "
            SELECT COUNT(*) as cnt FROM journal_entries 
            WHERE DATE(entry_date) = '2026-04-29' 
            AND description LIKE '%Listrik%'
            ";
            $checkStmt = $pdo->query($checkSql);
            $count = $checkStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
            
            if ($count > 1) {
                // Ada duplikasi, cek memo - yang benar punya memo "Pembayaran Beban"
                $checkMemSql = "
                SELECT COUNT(*) as cnt FROM journal_lines jl
                JOIN journal_entries je ON je.id = jl.journal_entry_id
                WHERE je.id = ? AND jl.memo LIKE '%operasional%'
                ";
                $checkMemStmt = $pdo->prepare($checkMemSql);
                $checkMemStmt->execute([$entry['id']]);
                $hasWrongMemo = $checkMemStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
                
                if ($hasWrongMemo > 0) {
                    echo "  ⚠️ ENTRY INI SALAH (memo 'operasional') - AKAN DIHAPUS\n";
                    $entriesToDelete[] = $entry['id'];
                }
            }
        }
        
        echo "\n";
    }
    
    // 2. Hapus entries yang salah
    if (count($entriesToDelete) > 0) {
        echo "2. MENGHAPUS ENTRIES YANG SALAH:\n";
        
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
            
            echo "  ✓ Entry " . $entryId . ": Deleted " . $linesDeleted . " lines\n";
        }
        
        echo "\n✓ Cleanup selesai!\n\n";
        
        // 3. Verifikasi hasil
        echo "3. VERIFIKASI HASIL:\n";
        $sql = "
        SELECT 
            je.id,
            je.entry_date,
            je.description,
            GROUP_CONCAT(CONCAT('Akun:', jl.account_id, ' Debit:', jl.debit) SEPARATOR ' | ') as lines_detail
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
            echo "  Entry " . $entry['id'] . " | " . $entry['entry_date'] . " | " . $entry['description'] . " | " . $entry['lines_detail'] . "\n";
        }
    } else {
        echo "Tidak ada entries yang perlu dihapus.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

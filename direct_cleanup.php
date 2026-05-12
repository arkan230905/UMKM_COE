<?php

$host = 'localhost';
$dbname = 'umkm_coe';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "FINAL CLEANUP - REMOVING ALL OLD DEPRECIATION DATA\n\n";
    
    // Step 1: Check existing data
    $stmt = $pdo->query("SELECT COUNT(*) FROM jurnal_umum WHERE tanggal = '2026-04-30' AND keterangan LIKE '%Penyusutan%'");
    $existing = $stmt->fetchColumn();
    echo "Existing depreciation entries: $existing\n";
    
    // Step 2: Delete ALL old depreciation entries
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        DELETE FROM jurnal_umum 
        WHERE tanggal = '2026-04-30' 
        AND (
            keterangan LIKE '%Penyusutan%' 
            OR keterangan LIKE '%GL) 2026-04%'
            OR keterangan LIKE '%SM) 2026-04%' 
            OR keterangan LIKE '%SYD) 2026-04%'
        )
    ");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "Deleted entries: $deleted\n";
    
    // Step 3: Insert correct values
    $correctEntries = [
        // Mesin - Rp 1.333.333
        ['coa_id' => 555, 'keterangan' => 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04', 'debit' => 1333333, 'kredit' => 0, 'referensi' => 'AST-MESIN'],
        ['coa_id' => 126, 'keterangan' => 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04', 'debit' => 0, 'kredit' => 1333333, 'referensi' => 'AST-MESIN'],
        
        // Peralatan - Rp 659.474 (CORRECTED)
        ['coa_id' => 553, 'keterangan' => 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04', 'debit' => 659474, 'kredit' => 0, 'referensi' => 'AST-PERALATAN'],
        ['coa_id' => 120, 'keterangan' => 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04', 'debit' => 0, 'kredit' => 659474, 'referensi' => 'AST-PERALATAN'],
        
        // Kendaraan - Rp 888.889
        ['coa_id' => 554, 'keterangan' => 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04', 'debit' => 888889, 'kredit' => 0, 'referensi' => 'AST-KENDARAAN'],
        ['coa_id' => 124, 'keterangan' => 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04', 'debit' => 0, 'kredit' => 888889, 'referensi' => 'AST-KENDARAAN']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at) 
        VALUES (?, '2026-04-30', ?, ?, ?, ?, 'depreciation', 1, NOW(), NOW())
    ");
    
    foreach ($correctEntries as $entry) {
        $stmt->execute([
            $entry['coa_id'],
            $entry['keterangan'],
            $entry['debit'],
            $entry['kredit'],
            $entry['referensi']
        ]);
    }
    
    $pdo->commit();
    
    echo "SUCCESS! Inserted " . count($correctEntries) . " correct entries\n";
    echo "Mesin: Rp 1.333.333\n";
    echo "Peralatan: Rp 659.474\n";
    echo "Kendaraan: Rp 888.889\n";
    echo "\nAll old (GL), (SM), (SYD) entries REMOVED!\n";
    
} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}

?>
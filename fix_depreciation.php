<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CURRENT DEPRECIATION VALUES ===\n";
    $stmt = $pdo->query("
        SELECT 
            keterangan, 
            debit, 
            kredit,
            CASE 
                WHEN keterangan LIKE '%Mesin%' THEN 'Mesin Produksi'
                WHEN keterangan LIKE '%Peralatan%' THEN 'Peralatan Produksi'
                WHEN keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
                ELSE 'Lainnya'
            END as kategori
        FROM jurnal_umum 
        WHERE tanggal = '2026-04-30' 
          AND keterangan LIKE '%Penyusutan%'
        ORDER BY debit DESC
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $amount = max($row['debit'], $row['kredit']);
        $type = $row['debit'] > 0 ? 'Debit' : 'Kredit';
        $kategori = $row['kategori'];
        
        echo "$kategori: $type Rp " . number_format($amount, 0, ',', '.') . "\n";
    }
    
    echo "\n=== FIXING VALUES ===\n";
    
    $pdo->beginTransaction();
    
    // Update Peralatan: 1333333 -> 659474
    $stmt = $pdo->prepare("UPDATE jurnal_umum SET debit = 659474.00 WHERE tanggal = '2026-04-30' AND keterangan LIKE '%Peralatan%' AND debit = 1333333.00");
    $stmt->execute();
    $updated1 = $stmt->rowCount();
    
    $stmt = $pdo->prepare("UPDATE jurnal_umum SET kredit = 659474.00 WHERE tanggal = '2026-04-30' AND keterangan LIKE '%Peralatan%' AND kredit = 1333333.00");
    $stmt->execute();
    $updated2 = $stmt->rowCount();
    
    echo "Peralatan - Debit: $updated1, Kredit: $updated2\n";
    
    // Update Kendaraan: 1333333 -> 888889
    $stmt = $pdo->prepare("UPDATE jurnal_umum SET debit = 888889.00 WHERE tanggal = '2026-04-30' AND keterangan LIKE '%Kendaraan%' AND debit = 1333333.00");
    $stmt->execute();
    $updated3 = $stmt->rowCount();
    
    $stmt = $pdo->prepare("UPDATE jurnal_umum SET kredit = 888889.00 WHERE tanggal = '2026-04-30' AND keterangan LIKE '%Kendaraan%' AND kredit = 1333333.00");
    $stmt->execute();
    $updated4 = $stmt->rowCount();
    
    echo "Kendaraan - Debit: $updated3, Kredit: $updated4\n";
    
    $totalUpdated = $updated1 + $updated2 + $updated3 + $updated4;
    
    if ($totalUpdated > 0) {
        $pdo->commit();
        echo "\nSUCCESS! Total $totalUpdated rows updated.\n\n";
        
        // Validate final results
        echo "=== FINAL RESULTS ===\n";
        $stmt = $pdo->query("
            SELECT 
                keterangan, 
                debit, 
                kredit,
                CASE 
                    WHEN keterangan LIKE '%Mesin%' THEN 'Mesin Produksi'
                    WHEN keterangan LIKE '%Peralatan%' THEN 'Peralatan Produksi'
                    WHEN keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
                    ELSE 'Lainnya'
                END as kategori
            FROM jurnal_umum 
            WHERE tanggal = '2026-04-30' 
              AND keterangan LIKE '%Penyusutan%'
            ORDER BY debit DESC
        ");
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $expectedValues = [
            'Mesin Produksi' => 1333333,
            'Peralatan Produksi' => 659474,
            'Kendaraan' => 888889
        ];
        
        foreach ($results as $row) {
            $amount = max($row['debit'], $row['kredit']);
            $type = $row['debit'] > 0 ? 'Debit' : 'Kredit';
            $kategori = $row['kategori'];
            
            $status = '❌';
            if (isset($expectedValues[$kategori]) && $amount == $expectedValues[$kategori]) {
                $status = '✅';
            }
            
            echo "$status $kategori: $type Rp " . number_format($amount, 0, ',', '.') . "\n";
        }
        
    } else {
        $pdo->rollback();
        echo "No data updated.\n";
    }
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
<?php

// Simple script to check purchase 4 data
echo "Checking purchase ID 4 from database...\n";

// Connect to database directly
$host = 'localhost';
$dbname = 'umkm_coe';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get purchase details
    $stmt = $pdo->prepare("
        SELECT pd.*, 
               bb.nama_bahan as bahan_baku_nama,
               bp.nama_bahan as bahan_pendukung_nama
        FROM pembelian_details pd
        LEFT JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
        LEFT JOIN bahan_pendukungs bp ON pd.bahan_pendukung_id = bp.id
        WHERE pd.pembelian_id = 4
    ");
    $stmt->execute();
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Purchase 4 details:\n";
    $hasBahanBaku = false;
    $hasBahanPendukung = false;
    
    foreach ($details as $detail) {
        echo "- Detail ID: {$detail['id']}\n";
        if ($detail['bahan_baku_id']) {
            echo "  Bahan Baku: {$detail['bahan_baku_nama']}\n";
            $hasBahanBaku = true;
        }
        if ($detail['bahan_pendukung_id']) {
            echo "  Bahan Pendukung: {$detail['bahan_pendukung_nama']}\n";
            $hasBahanPendukung = true;
        }
    }
    
    echo "\nAnalysis:\n";
    echo "Has Bahan Baku: " . ($hasBahanBaku ? 'Yes' : 'No') . "\n";
    echo "Has Bahan Pendukung: " . ($hasBahanPendukung ? 'Yes' : 'No') . "\n";
    
    // Determine category
    if ($hasBahanBaku && !$hasBahanPendukung) {
        $kategori = 'bahan_baku';
    } elseif ($hasBahanPendukung && !$hasBahanBaku) {
        $kategori = 'bahan_pendukung';
    } else {
        $kategori = 'mixed';
    }
    
    echo "Category: {$kategori}\n";
    
    // Check what should be displayed
    $showBahanBaku = ($kategori !== 'bahan_pendukung') ? 'block' : 'none';
    $showBahanPendukung = ($kategori !== 'bahan_baku') ? 'block' : 'none';
    
    echo "\nExpected Display:\n";
    echo "Bahan Baku section: {$showBahanBaku}\n";
    echo "Bahan Pendukung section: {$showBahanPendukung}\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
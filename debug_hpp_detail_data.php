<?php

echo "=== DEBUG HPP DETAIL DATA ===\n\n";

// Connect to database
require_once 'vendor/autoload.php';

// Create database connection
$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully\n\n";
    
    // Check BomJobCosting for product ID 2
    echo "1. Checking BomJobCosting for Product ID 2:\n";
    $stmt = $pdo->prepare("SELECT * FROM bom_job_costings WHERE produk_id = 2");
    $stmt->execute();
    $bomJobCosting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($bomJobCosting) {
        echo "✅ BomJobCosting found:\n";
        echo "   ID: " . $bomJobCosting['id'] . "\n";
        echo "   Produk ID: " . $bomJobCosting['produk_id'] . "\n";
        echo "   Total BBB: " . $bomJobCosting['total_bbb'] . "\n";
        echo "   Total BTKL: " . $bomJobCosting['total_btkl'] . "\n";
        echo "   Total BOP: " . $bomJobCosting['total_bop'] . "\n";
        echo "   Selected BBB IDs: " . $bomJobCosting['selected_bbb_ids'] . "\n";
        echo "   Selected BTKL IDs: " . $bomJobCosting['selected_btkl_ids'] . "\n";
        echo "   Selected BOP IDs: " . $bomJobCosting['selected_bop_ids'] . "\n";
    } else {
        echo "❌ No BomJobCosting found for Product ID 2\n";
    }
    
    echo "\n2. Checking BBB Data for Product ID 2:\n";
    $stmt = $pdo->prepare("SELECT * FROM bom_job_bbb WHERE produk_id = 2");
    $stmt->execute();
    $bbbData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "BBB Records found: " . count($bbbData) . "\n";
    foreach ($bbbData as $bbb) {
        echo "   ID: " . $bbb['id'] . ", Bahan: " . $bbb['bahan_baku_id'] . ", Subtotal: " . $bbb['subtotal'] . "\n";
    }
    
    echo "\n3. Checking BTKL Data for BomJobCosting ID " . ($bomJobCosting['id'] ?? 'null') . ":\n";
    if ($bomJobCosting) {
        $stmt = $pdo->prepare("SELECT * FROM bom_job_btkl WHERE bom_job_costing_id = ?");
        $stmt->execute([$bomJobCosting['id']]);
        $btklData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "BTKL Records found: " . count($btklData) . "\n";
        foreach ($btklData as $btkl) {
            echo "   ID: " . $btkl['id'] . ", Proses: " . $btkl['nama_proses'] . ", Subtotal: " . $btkl['subtotal'] . "\n";
        }
    } else {
        echo "❌ No BomJobCosting to check BTKL data\n";
    }
    
    echo "\n4. Checking BOP Data for BomJobCosting ID " . ($bomJobCosting['id'] ?? 'null') . ":\n";
    if ($bomJobCosting) {
        $stmt = $pdo->prepare("SELECT * FROM bom_job_bop WHERE bom_job_costing_id = ?");
        $stmt->execute([$bomJobCosting['id']]);
        $bopData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "BOP Records found: " . count($bopData) . "\n";
        foreach ($bopData as $bop) {
            echo "   ID: " . $bop['id'] . ", Nama: " . $bop['nama_bop'] . ", Tarif: " . $bop['tarif'] . "\n";
        }
    } else {
        echo "❌ No BomJobCosting to check BOP data\n";
    }
    
    echo "\n5. Checking BOP Data from bop_proses table:\n";
    if ($bomJobCosting && $bomJobCosting['selected_btkl_ids']) {
        $selectedBtklIds = explode(',', $bomJobCosting['selected_btkl_ids']);
        echo "Selected BTKL IDs: " . implode(', ', $selectedBtklIds) . "\n";
        
        if (!empty($selectedBtklIds) && $selectedBtklIds[0] !== '') {
            $placeholders = str_repeat('?,', count($selectedBtklIds) - 1) . '?';
            $stmt = $pdo->prepare("SELECT * FROM bop_proses WHERE proses_btkl_id IN ($placeholders)");
            $stmt->execute($selectedBtklIds);
            $bopProsesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "BOP Proses Records found: " . count($bopProsesData) . "\n";
            foreach ($bopProsesData as $bop) {
                echo "   ID: " . $bop['id'] . ", Komponen: " . $bop['nama_komponen'] . ", Tarif: " . $bop['tarif'] . "\n";
            }
        } else {
            echo "❌ No selected BTKL IDs found\n";
        }
    } else {
        echo "❌ No selected BTKL IDs to check\n";
    }
    
    echo "\n=== ANALYSIS ===\n";
    echo "If BBB data exists but BTKL/BOP don't appear:\n";
    echo "1. Check if BTKL and BOP data was actually saved during HPP creation\n";
    echo "2. Check if selected_btkl_ids and selected_bop_ids are properly stored\n";
    echo "3. Check if controller is loading data from correct tables\n";
    echo "4. Check if view conditions are working properly\n\n";
    
    echo "Next steps:\n";
    echo "1. Access the HPP detail page to trigger debug logging\n";
    echo "2. Check Laravel logs for debug information\n";
    echo "3. Verify data counts and content\n";
    echo "4. Fix any issues found in data loading\n";
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";

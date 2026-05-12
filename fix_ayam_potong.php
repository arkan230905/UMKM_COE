<?php

// Simple PHP script to fix Ayam Potong data
// Run this with: php fix_ayam_potong.php

echo "🔥 FIXING AYAM POTONG ID=1: 120→160 POTONG\n";
echo "=====================================\n\n";

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'umkm_coe';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check current data
    echo "📋 CHECKING CURRENT DATA:\n";
    echo "-------------------------\n";
    
    // Check bahan_baku ID=1
    $stmt = $pdo->prepare("SELECT id, nama_bahan, satuan_id, saldo_awal FROM bahan_bakus WHERE id = 1");
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        echo "Item ID=1: {$item['nama_bahan']}\n";
        echo "Satuan ID: {$item['satuan_id']}\n";
        echo "Saldo Awal: {$item['saldo_awal']}\n\n";
    }
    
    // Check production details
    $stmt = $pdo->prepare("SELECT id, produksi_id, qty_resep, satuan_resep FROM produksi_details WHERE bahan_baku_id = 1");
    $stmt->execute();
    $prodDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Production Details for ID=1:\n";
    foreach ($prodDetails as $detail) {
        echo "- Detail ID {$detail['id']}: {$detail['qty_resep']} {$detail['satuan_resep']}\n";
    }
    echo "\n";
    
    // Check stock movements
    $stmt = $pdo->prepare("SELECT id, tanggal, ref_id, qty, qty_as_input, satuan_as_input FROM stock_movements WHERE item_type = 'material' AND item_id = 1 AND ref_type = 'production'");
    $stmt->execute();
    $stockMovements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Stock Movements for ID=1:\n";
    foreach ($stockMovements as $movement) {
        echo "- Movement ID {$movement['id']}: {$movement['qty_as_input']} {$movement['satuan_as_input']}\n";
    }
    echo "\n";
    
    // Apply fixes
    echo "🔧 APPLYING FIXES:\n";
    echo "------------------\n";
    
    // Update production details
    $stmt = $pdo->prepare("UPDATE produksi_details SET qty_resep = 160, satuan_resep = 'Potong' WHERE bahan_baku_id = 1");
    $result = $stmt->execute();
    $affected = $stmt->rowCount();
    echo "✅ Updated {$affected} production detail records\n";
    
    // Update stock movements
    $stmt = $pdo->prepare("UPDATE stock_movements SET qty_as_input = 160, satuan_as_input = 'Potong' WHERE item_type = 'material' AND item_id = 1 AND ref_type = 'production'");
    $result = $stmt->execute();
    $affected = $stmt->rowCount();
    echo "✅ Updated {$affected} stock movement records\n\n";
    
    // Verify changes
    echo "✅ VERIFICATION:\n";
    echo "---------------\n";
    
    // Check production details after update
    $stmt = $pdo->prepare("SELECT id, produksi_id, qty_resep, satuan_resep FROM produksi_details WHERE bahan_baku_id = 1");
    $stmt->execute();
    $prodDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Production Details after fix:\n";
    foreach ($prodDetails as $detail) {
        echo "- Detail ID {$detail['id']}: {$detail['qty_resep']} {$detail['satuan_resep']}\n";
    }
    echo "\n";
    
    // Check stock movements after update
    $stmt = $pdo->prepare("SELECT id, tanggal, ref_id, qty, qty_as_input, satuan_as_input FROM stock_movements WHERE item_type = 'material' AND item_id = 1 AND ref_type = 'production'");
    $stmt->execute();
    $stockMovements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Stock Movements after fix:\n";
    foreach ($stockMovements as $movement) {
        echo "- Movement ID {$movement['id']}: {$movement['qty_as_input']} {$movement['satuan_as_input']}\n";
    }
    echo "\n";
    
    // Check if all are correct
    $allCorrect = true;
    foreach ($prodDetails as $detail) {
        if ($detail['qty_resep'] != 160 || $detail['satuan_resep'] != 'Potong') {
            $allCorrect = false;
            break;
        }
    }
    foreach ($stockMovements as $movement) {
        if ($movement['qty_as_input'] != 160 || $movement['satuan_as_input'] != 'Potong') {
            $allCorrect = false;
            break;
        }
    }
    
    if ($allCorrect) {
        echo "🎉 SUCCESS! ALL DATA FIXED TO 160 POTONG!\n";
        echo "=========================================\n\n";
        echo "📝 NEXT STEPS:\n";
        echo "1. Clear application cache\n";
        echo "2. Test the URL: laporan/stok?tipe=material&item_id=1&satuan_id=22\n";
        echo "3. Verify it shows 160 Potong instead of 120 Potong\n";
    } else {
        echo "❌ SOME DATA STILL INCORRECT!\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
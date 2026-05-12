<?php
// Simple verification script to check if the fix was applied correctly
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Ayam Potong Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        .code { background: #f8f8f8; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: green;">✅ Verify Ayam Potong Fix</h1>
        <p><strong>Checking if the fix was applied correctly...</strong></p>
        
        <?php
        try {
            // Database connection
            $host = 'localhost';
            $dbname = 'umkm_coe';
            $username = 'root';
            $password = '';
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<div class='section'>";
            echo "<h2>📋 Current Database Status</h2>";
            
            // Check bahan_baku ID=1
            $stmt = $pdo->prepare("SELECT id, nama_bahan, satuan_id, saldo_awal FROM bahan_bakus WHERE id = 1");
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item) {
                echo "<p><strong>Item ID=1:</strong> {$item['nama_bahan']}</p>";
            }
            
            // Check production details
            $stmt = $pdo->prepare("SELECT id, produksi_id, qty_resep, satuan_resep FROM produksi_details WHERE bahan_baku_id = 1");
            $stmt->execute();
            $prodDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Production Details:</h3>";
            if (count($prodDetails) > 0) {
                echo "<table>";
                echo "<tr><th>Detail ID</th><th>Produksi ID</th><th>Qty Resep</th><th>Satuan Resep</th><th>Status</th></tr>";
                
                $allProdCorrect = true;
                foreach ($prodDetails as $detail) {
                    $status = ($detail['qty_resep'] == 160 && $detail['satuan_resep'] == 'Potong') ? 
                        "<span class='success'>✅ CORRECT</span>" : 
                        "<span class='error'>❌ INCORRECT</span>";
                    
                    if ($detail['qty_resep'] != 160 || $detail['satuan_resep'] != 'Potong') {
                        $allProdCorrect = false;
                    }
                    
                    echo "<tr>";
                    echo "<td>{$detail['id']}</td>";
                    echo "<td>{$detail['produksi_id']}</td>";
                    echo "<td>{$detail['qty_resep']}</td>";
                    echo "<td>{$detail['satuan_resep']}</td>";
                    echo "<td>{$status}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>No production details found</p>";
                $allProdCorrect = false;
            }
            
            // Check stock movements
            $stmt = $pdo->prepare("SELECT id, tanggal, ref_id, qty, qty_as_input, satuan_as_input FROM stock_movements WHERE item_type = 'material' AND item_id = 1 AND ref_type = 'production'");
            $stmt->execute();
            $stockMovements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Stock Movements:</h3>";
            if (count($stockMovements) > 0) {
                echo "<table>";
                echo "<tr><th>Movement ID</th><th>Tanggal</th><th>Ref ID</th><th>Qty</th><th>Qty as Input</th><th>Satuan as Input</th><th>Status</th></tr>";
                
                $allMovCorrect = true;
                foreach ($stockMovements as $movement) {
                    $status = ($movement['qty_as_input'] == 160 && $movement['satuan_as_input'] == 'Potong') ? 
                        "<span class='success'>✅ CORRECT</span>" : 
                        "<span class='error'>❌ INCORRECT</span>";
                    
                    if ($movement['qty_as_input'] != 160 || $movement['satuan_as_input'] != 'Potong') {
                        $allMovCorrect = false;
                    }
                    
                    echo "<tr>";
                    echo "<td>{$movement['id']}</td>";
                    echo "<td>{$movement['tanggal']}</td>";
                    echo "<td>{$movement['ref_id']}</td>";
                    echo "<td>{$movement['qty']}</td>";
                    echo "<td>{$movement['qty_as_input']}</td>";
                    echo "<td>{$movement['satuan_as_input']}</td>";
                    echo "<td>{$status}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>No stock movements found</p>";
                $allMovCorrect = false;
            }
            
            echo "</div>";
            
            // Overall status
            echo "<div class='section'>";
            echo "<h2>🎯 Overall Status</h2>";
            
            if (isset($allProdCorrect) && isset($allMovCorrect) && $allProdCorrect && $allMovCorrect) {
                echo "<h2 class='success'>🎉 SUCCESS! ALL DATA IS CORRECT!</h2>";
                echo "<p class='success'>✅ Production details show 160 Potong</p>";
                echo "<p class='success'>✅ Stock movements show 160 Potong</p>";
                echo "<p class='info'><strong>Next step:</strong> Test the stock report page</p>";
                echo "<p><a href='/laporan/stok?tipe=material&item_id=1&satuan_id=22' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Stock Report</a></p>";
            } else {
                echo "<h2 class='error'>❌ FIX NOT APPLIED YET</h2>";
                echo "<p class='error'>The database still contains incorrect data.</p>";
                echo "<p class='info'><strong>Please execute the SQL commands from MANUAL_FIX_INSTRUCTIONS.md</strong></p>";
            }
            
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='section'>";
            echo "<h2 class='error'>❌ ERROR</h2>";
            echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
        ?>
        
        <div class="section">
            <h2>📝 Instructions</h2>
            <p>If the fix is not applied yet:</p>
            <ol>
                <li>Open phpMyAdmin or your MySQL client</li>
                <li>Select the "umkm_coe" database</li>
                <li>Execute these SQL commands:</li>
            </ol>
            <div class="code">
UPDATE produksi_details SET qty_resep = 160, satuan_resep = 'Potong' WHERE bahan_baku_id = 1;<br>
UPDATE stock_movements SET qty_as_input = 160, satuan_as_input = 'Potong' WHERE item_type = 'material' AND item_id = 1 AND ref_type = 'production';
            </div>
            <p>Then refresh this page to verify the fix.</p>
        </div>
    </div>
</body>
</html>
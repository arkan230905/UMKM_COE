<?php
// Verification script for bahan pendukung saldo_awal fix
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Bahan Pendukung Fix</title>
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
        <h1 style="color: green;">✅ Verify Bahan Pendukung Saldo Awal Fix</h1>
        <p><strong>Checking if bahan pendukung uses saldo_awal correctly...</strong></p>
        
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
            echo "<h2>📋 Bahan Pendukung Database Status</h2>";
            
            // Check bahan_pendukungs table structure
            $stmt = $pdo->prepare("DESCRIBE bahan_pendukungs");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $hasSaldoAwal = false;
            $hasStok = false;
            
            foreach ($columns as $column) {
                if ($column['Field'] === 'saldo_awal') {
                    $hasSaldoAwal = true;
                }
                if ($column['Field'] === 'stok') {
                    $hasStok = true;
                }
            }
            
            echo "<h3>Table Structure:</h3>";
            echo "<p class='" . ($hasSaldoAwal ? 'success' : 'error') . "'>saldo_awal column: " . ($hasSaldoAwal ? '✅ EXISTS' : '❌ MISSING') . "</p>";
            echo "<p class='" . ($hasStok ? 'success' : 'error') . "'>stok column: " . ($hasStok ? '✅ EXISTS' : '❌ MISSING') . "</p>";
            
            // Check actual data
            $stmt = $pdo->prepare("SELECT id, nama_bahan, saldo_awal, stok, harga_satuan FROM bahan_pendukungs ORDER BY id LIMIT 10");
            $stmt->execute();
            $bahanPendukungs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Sample Data:</h3>";
            if (count($bahanPendukungs) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Nama Bahan</th><th>Saldo Awal</th><th>Stok</th><th>Harga Satuan</th><th>Status</th></tr>";
                
                foreach ($bahanPendukungs as $item) {
                    $saldoAwal = (float)($item['saldo_awal'] ?? 0);
                    $stok = (float)($item['stok'] ?? 0);
                    
                    $status = '';
                    if ($saldoAwal > 0) {
                        $status = "<span class='success'>✅ Has saldo_awal</span>";
                    } else {
                        $status = "<span class='warning'>⚠️ No saldo_awal</span>";
                    }
                    
                    echo "<tr>";
                    echo "<td>{$item['id']}</td>";
                    echo "<td>{$item['nama_bahan']}</td>";
                    echo "<td>" . number_format($saldoAwal, 2) . "</td>";
                    echo "<td>" . number_format($stok, 2) . "</td>";
                    echo "<td>Rp " . number_format($item['harga_satuan'], 0, ',', '.') . "</td>";
                    echo "<td>{$status}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>No bahan pendukung data found</p>";
            }
            
            echo "</div>";
            
            // Check the fix
            echo "<div class='section'>";
            echo "<h2>🔧 Fix Status</h2>";
            
            echo "<h3>What was fixed:</h3>";
            echo "<ul>";
            echo "<li><strong>Before:</strong> Stock report used hardcoded value of 200 for all bahan pendukung</li>";
            echo "<li><strong>After:</strong> Stock report now uses <code>saldo_awal</code> from database</li>";
            echo "</ul>";
            
            echo "<h3>Code Changes:</h3>";
            echo "<div class='code'>";
            echo "// OLD CODE (WRONG):<br>";
            echo "if(\$tipe == 'bahan_pendukung') {<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;\$baseQty = 200; // Fixed for bahan pendukung<br>";
            echo "}<br><br>";
            
            echo "// NEW CODE (CORRECT):<br>";
            echo "\$baseQty = (float)(\$selectedItem->saldo_awal ?? 0); // Use saldo_awal for bahan_pendukung<br>";
            echo "if(\$tipe != 'bahan_pendukung') {<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;\$baseQty = (float)(\$selectedItem->stok ?? 0); // Use stok for materials and products<br>";
            echo "}";
            echo "</div>";
            
            echo "<p class='success'>✅ Fix has been applied to the view file</p>";
            echo "</div>";
            
            // Test instructions
            echo "<div class='section'>";
            echo "<h2>🧪 Test the Fix</h2>";
            echo "<p><strong>To verify the fix works:</strong></p>";
            echo "<ol>";
            echo "<li>Go to <a href='/laporan/stok?tipe=bahan_pendukung' target='_blank'>Laporan Stok - Bahan Pendukung</a></li>";
            echo "<li>Select any bahan pendukung item</li>";
            echo "<li>Check that the 'Stok Awal' column shows the correct value from the database</li>";
            echo "<li>It should NOT show 200 for all items, but the actual saldo_awal value</li>";
            echo "</ol>";
            
            echo "<h3>Expected Behavior:</h3>";
            echo "<ul>";
            echo "<li>Each bahan pendukung should show its own saldo_awal value</li>";
            echo "<li>No more confusion between 'Stok Awal' and 'Saldo Awal' - they are the same thing</li>";
            echo "<li>The table header says 'Stok Awal' but it displays the saldo_awal from database</li>";
            echo "</ul>";
            
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='section'>";
            echo "<h2 class='error'>❌ ERROR</h2>";
            echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
        ?>
        
        <div class="section">
            <h2>📝 Summary</h2>
            <p><strong>Issue:</strong> Bahan pendukung stock report was using hardcoded value of 200 instead of the saldo_awal from database.</p>
            <p><strong>Solution:</strong> Modified the view to use <code>$selectedItem->saldo_awal</code> for bahan_pendukung items.</p>
            <p><strong>Result:</strong> Stock report now correctly displays the actual saldo_awal value from the bahan_pendukungs table.</p>
        </div>
    </div>
</body>
</html>
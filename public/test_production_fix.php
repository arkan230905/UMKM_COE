<!DOCTYPE html>
<html>
<head>
    <title>Test Production Fix - 160 Potong</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        .info { color: blue; }
        .warning { color: orange; font-weight: bold; }
        .code { background: #f8f8f8; padding: 10px; border-radius: 3px; font-family: monospace; }
        .test-link { display: inline-block; margin: 10px; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .test-link:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Test Production Fix: 160 Potong Issue</h1>
        <p><strong>Problem:</strong> Laporan stok multi-satuan menunjukkan 120 Potong, padahal transaksi produksi menggunakan 160 Potong.</p>
        
        <?php
        try {
            require_once '../vendor/autoload.php';
            $app = require_once '../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            echo "<div class='section'>";
            echo "<h2>📊 Data Analysis</h2>";
            
            // Check production movement for Ayam Potong
            $productionMovement = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 2) // Ayam Potong ID
                ->where('ref_type', 'production')
                ->first();
            
            if ($productionMovement) {
                echo "<p><strong>Production Stock Movement:</strong></p>";
                echo "<div class='code'>";
                echo "Qty (converted): {$productionMovement->qty}<br>";
                echo "Qty as Input (original): " . ($productionMovement->qty_as_input ?? 'null') . "<br>";
                echo "Satuan as Input (original): " . ($productionMovement->satuan_as_input ?? 'null') . "<br>";
                echo "Direction: {$productionMovement->direction}<br>";
                echo "Date: {$productionMovement->tanggal}";
                echo "</div>";
                
                if ($productionMovement->qty_as_input == 160 && $productionMovement->satuan_as_input == 'Potong') {
                    echo "<p class='success'>✅ Original production data is correct: 160 Potong</p>";
                } else {
                    echo "<p class='error'>❌ Original production data is incorrect</p>";
                    echo "<p>Expected: 160 Potong</p>";
                    echo "<p>Found: {$productionMovement->qty_as_input} {$productionMovement->satuan_as_input}</p>";
                }
            } else {
                echo "<p class='error'>❌ No production movement found for Ayam Potong</p>";
            }
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<h2>🧪 Test Links</h2>";
            echo "<p>Test the fix by accessing these pages:</p>";
            
            echo "<a href='/transaksi/produksi/2' class='test-link' target='_blank'>📋 Transaksi Produksi ID 2</a>";
            echo "<p class='info'>Should show: 160 Potong for Ayam Potong</p>";
            
            echo "<a href='/laporan/stok?tipe=material&item_id=2&satuan_id=22' class='test-link' target='_blank'>📊 Laporan Stok - Ayam Potong (Potong)</a>";
            echo "<p class='info'>Should now show: 160 Potong in Pemakaian Produksi column</p>";
            
            echo "<a href='/laporan/stok?tipe=material&item_id=2&satuan_id=2' class='test-link' target='_blank'>📊 Laporan Stok - Ayam Potong (Kilogram)</a>";
            echo "<p class='info'>Should show: 53.33 Kilogram (160 ÷ 3) in Pemakaian Produksi column</p>";
            
            echo "<a href='/laporan/kartu-stok?item_type=bahan_baku&item_id=2' class='test-link' target='_blank'>📋 Kartu Stok - Ayam Potong</a>";
            echo "<p class='info'>Should show: Pemakaian Produksi - 160.00 Potong</p>";
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<h2>🔍 What Was Fixed</h2>";
            echo "<ol>";
            echo "<li><strong>LaporanController:</strong> Modified to use qty_as_input for production movements</li>";
            echo "<li><strong>View Logic:</strong> Added special handling for production data to use original transaction values</li>";
            echo "<li><strong>StockService:</strong> Updated getStockReport to use stock_movements data with qty_as_input</li>";
            echo "</ol>";
            
            echo "<h3>Expected Results:</h3>";
            echo "<ul>";
            echo "<li><strong>Satuan Potong:</strong> Should show 160 Potong (original transaction data)</li>";
            echo "<li><strong>Satuan Kilogram:</strong> Should show 53.33 Kg (160 ÷ 3 conversion)</li>";
            echo "<li><strong>Satuan Gram:</strong> Should show 53,333 Gram (53.33 × 1000)</li>";
            echo "<li><strong>Satuan Ons:</strong> Should show 533.3 Ons (53.33 × 10)</li>";
            echo "</ul>";
            echo "</div>";
            
            if ($productionMovement && $productionMovement->qty_as_input == 160) {
                echo "<div class='section'>";
                echo "<h2 class='success'>✅ Fix Status: READY</h2>";
                echo "<p class='success'>Data is correct. The fix should now work properly.</p>";
                echo "<p>Please test the links above to verify that all pages now show consistent data.</p>";
                echo "</div>";
            } else {
                echo "<div class='section'>";
                echo "<h2 class='error'>❌ Fix Status: NEEDS DATA UPDATE</h2>";
                echo "<p class='error'>The stock_movements table needs to be updated with correct qty_as_input data.</p>";
                echo "<p>Run the production fix script first to update the data.</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='section'>";
            echo "<h2 class='error'>❌ Error</h2>";
            echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
            echo "<p>File: " . $e->getFile() . "</p>";
            echo "<p>Line: " . $e->getLine() . "</p>";
            echo "</div>";
        }
        ?>
        
        <div class="section">
            <h2>📝 Summary</h2>
            <p>Perbaikan yang telah dilakukan:</p>
            <ol>
                <li><strong>Root Cause:</strong> Laporan stok menggunakan data yang sudah dikonversi (qty) bukan data asli transaksi (qty_as_input)</li>
                <li><strong>Solution:</strong> Modifikasi controller dan view untuk menggunakan qty_as_input dan satuan_as_input untuk data produksi</li>
                <li><strong>Result:</strong> Sekarang semua laporan akan menampilkan data yang konsisten dengan halaman transaksi produksi</li>
            </ol>
            
            <p class='info'><strong>Note:</strong> Jika masih ada perbedaan, pastikan data di tabel stock_movements sudah benar dengan menjalankan script perbaikan data terlebih dahulu.</p>
        </div>
    </div>
</body>
</html>
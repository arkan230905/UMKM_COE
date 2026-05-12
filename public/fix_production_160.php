<!DOCTYPE html>
<html>
<head>
    <title>Fix Production 160 Potong Issue</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        .info { color: blue; }
        .code { background: #f8f8f8; padding: 10px; border-radius: 3px; font-family: monospace; }
        .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Production 160 Potong Issue</h1>
        <p><strong>Problem:</strong> Halaman transaksi produksi menunjukkan 160 potong, tetapi laporan stok menunjukkan nilai yang berbeda.</p>
        
        <?php
        try {
            require_once '../vendor/autoload.php';
            $app = require_once '../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            echo "<div class='section'>";
            echo "<h2>📊 Current Data Analysis</h2>";
            
            // Check production detail
            $produksiDetail = DB::table('produksi_details')
                ->join('bahan_bakus', 'produksi_details.bahan_baku_id', '=', 'bahan_bakus.id')
                ->where('produksi_details.produksi_id', 2)
                ->where('bahan_bakus.nama_bahan', 'Ayam Potong')
                ->select('produksi_details.*', 'bahan_bakus.nama_bahan')
                ->first();
            
            if ($produksiDetail) {
                echo "<p><strong>Production Detail (Transaksi Produksi):</strong></p>";
                echo "<div class='code'>";
                echo "Qty Resep: {$produksiDetail->qty_resep} {$produksiDetail->satuan_resep}<br>";
                echo "Qty Konversi: {$produksiDetail->qty_konversi}<br>";
                echo "Subtotal: Rp " . number_format($produksiDetail->subtotal);
                echo "</div>";
            } else {
                echo "<p class='error'>❌ No production detail found for Ayam Potong in production ID 2</p>";
            }
            
            // Check stock movement
            $stockMovement = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 2) // Ayam Potong ID
                ->where('ref_type', 'production')
                ->where('ref_id', 2)
                ->first();
            
            if ($stockMovement) {
                echo "<p><strong>Stock Movement (Laporan Stok):</strong></p>";
                echo "<div class='code'>";
                echo "Qty: {$stockMovement->qty}<br>";
                echo "Qty as Input: " . ($stockMovement->qty_as_input ?? 'null') . "<br>";
                echo "Satuan as Input: " . ($stockMovement->satuan_as_input ?? 'null') . "<br>";
                echo "Direction: {$stockMovement->direction}";
                echo "</div>";
            } else {
                echo "<p class='error'>❌ No stock movement found for production ID 2</p>";
            }
            echo "</div>";
            
            // Check if fix is needed
            $needsFix = false;
            if ($produksiDetail && $stockMovement) {
                if ($stockMovement->qty_as_input != $produksiDetail->qty_resep || 
                    $stockMovement->satuan_as_input != $produksiDetail->satuan_resep) {
                    $needsFix = true;
                }
            }
            
            if ($needsFix) {
                echo "<div class='section'>";
                echo "<h2>🔧 Applying Fix</h2>";
                
                // Apply the fix
                $updated = DB::table('stock_movements')
                    ->where('item_type', 'material')
                    ->where('item_id', 2)
                    ->where('ref_type', 'production')
                    ->where('ref_id', 2)
                    ->update([
                        'qty_as_input' => $produksiDetail->qty_resep,
                        'satuan_as_input' => $produksiDetail->satuan_resep
                    ]);
                
                if ($updated > 0) {
                    echo "<p class='success'>✅ Stock movement updated successfully!</p>";
                    echo "<div class='code'>";
                    echo "Updated qty_as_input: {$produksiDetail->qty_resep}<br>";
                    echo "Updated satuan_as_input: {$produksiDetail->satuan_resep}";
                    echo "</div>";
                } else {
                    echo "<p class='error'>❌ Failed to update stock movement</p>";
                }
                echo "</div>";
            } else {
                echo "<div class='section'>";
                echo "<h2>✅ No Fix Needed</h2>";
                echo "<p class='success'>Data is already consistent between production detail and stock movement.</p>";
                echo "</div>";
            }
            
            echo "<div class='section'>";
            echo "<h2>🧪 Test Results</h2>";
            echo "<p>Now both pages should show the same data:</p>";
            echo "<ul>";
            echo "<li><a href='/transaksi/produksi/2' target='_blank'>Transaksi Produksi ID 2</a> - Should show: {$produksiDetail->qty_resep} {$produksiDetail->satuan_resep}</li>";
            echo "<li><a href='/laporan/kartu-stok?item_type=bahan_baku&item_id=2' target='_blank'>Kartu Stok Ayam Potong</a> - Should show: Pemakaian Produksi - {$produksiDetail->qty_resep} {$produksiDetail->satuan_resep}</li>";
            echo "</ul>";
            echo "</div>";
            
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
            <p>Masalah ini terjadi karena data di tabel <code>stock_movements</code> tidak sinkron dengan data di tabel <code>produksi_details</code>.</p>
            <p>Perbaikan yang dilakukan:</p>
            <ol>
                <li>Mengupdate field <code>qty_as_input</code> dan <code>satuan_as_input</code> di <code>stock_movements</code></li>
                <li>Memastikan data sesuai dengan <code>qty_resep</code> dan <code>satuan_resep</code> dari <code>produksi_details</code></li>
                <li>Sekarang kartu stok akan menampilkan data yang sama dengan halaman transaksi produksi</li>
            </ol>
        </div>
    </div>
</body>
</html>
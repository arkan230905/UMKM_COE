<!DOCTYPE html>
<html>
<head>
    <title>FORCE FIX: 120 → 160 POTONG</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        .code { background: #f8f8f8; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: red;">🔥 FORCE FIX: 120 → 160 POTONG</h1>
        <p><strong>MASALAH:</strong> Data masih menunjukkan 120 Potong padahal seharusnya 160 Potong!</p>
        
        <?php
        try {
            require_once '../vendor/autoload.php';
            $app = require_once '../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            echo "<div class='section'>";
            echo "<h2>🔍 DIAGNOSIS</h2>";
            
            // Check current stock movement
            $stockMovement = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 2) // Ayam Potong
                ->where('ref_type', 'production')
                ->first();
            
            if ($stockMovement) {
                echo "<div class='code'>";
                echo "<strong>Current Stock Movement:</strong><br>";
                echo "ID: {$stockMovement->id}<br>";
                echo "Qty: {$stockMovement->qty}<br>";
                echo "Qty as Input: " . ($stockMovement->qty_as_input ?? 'NULL') . "<br>";
                echo "Satuan as Input: " . ($stockMovement->satuan_as_input ?? 'NULL') . "<br>";
                echo "Ref ID: {$stockMovement->ref_id}<br>";
                echo "Date: {$stockMovement->tanggal}";
                echo "</div>";
                
                // Check production detail
                $prodDetail = DB::table('produksi_details')
                    ->join('bahan_bakus', 'produksi_details.bahan_baku_id', '=', 'bahan_bakus.id')
                    ->where('produksi_details.produksi_id', $stockMovement->ref_id)
                    ->where('bahan_bakus.nama_bahan', 'Ayam Potong')
                    ->select('produksi_details.*')
                    ->first();
                
                if ($prodDetail) {
                    echo "<div class='code'>";
                    echo "<strong>Production Detail:</strong><br>";
                    echo "Qty Resep: {$prodDetail->qty_resep}<br>";
                    echo "Satuan Resep: {$prodDetail->satuan_resep}<br>";
                    echo "Qty Konversi: {$prodDetail->qty_konversi}";
                    echo "</div>";
                }
            }
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<h2>🔧 APPLYING FORCE FIX</h2>";
            
            // FORCE UPDATE: Set production detail to 160 Potong
            if ($prodDetail) {
                echo "<p class='warning'>Updating production detail to 160 Potong...</p>";
                
                $updateDetail = DB::table('produksi_details')
                    ->where('id', $prodDetail->id)
                    ->update([
                        'qty_resep' => 160,
                        'satuan_resep' => 'Potong'
                    ]);
                
                if ($updateDetail) {
                    echo "<p class='success'>✅ Production detail updated!</p>";
                } else {
                    echo "<p class='error'>❌ Failed to update production detail</p>";
                }
            }
            
            // FORCE UPDATE: Set stock movement qty_as_input to 160 Potong
            if ($stockMovement) {
                echo "<p class='warning'>Updating stock movement to 160 Potong...</p>";
                
                $updateMovement = DB::table('stock_movements')
                    ->where('id', $stockMovement->id)
                    ->update([
                        'qty_as_input' => 160,
                        'satuan_as_input' => 'Potong'
                    ]);
                
                if ($updateMovement) {
                    echo "<p class='success'>✅ Stock movement updated!</p>";
                } else {
                    echo "<p class='error'>❌ Failed to update stock movement</p>";
                }
            }
            
            // Clear cache
            echo "<p class='warning'>Clearing cache...</p>";
            try {
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('view:clear');
                echo "<p class='success'>✅ Cache cleared!</p>";
            } catch (Exception $e) {
                echo "<p class='warning'>⚠️ Cache clear failed: " . $e->getMessage() . "</p>";
            }
            
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<h2>✅ VERIFICATION</h2>";
            
            // Verify the changes
            $verifyMovement = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 2)
                ->where('ref_type', 'production')
                ->first();
            
            $verifyDetail = DB::table('produksi_details')
                ->join('bahan_bakus', 'produksi_details.bahan_baku_id', '=', 'bahan_bakus.id')
                ->where('produksi_details.produksi_id', $verifyMovement->ref_id)
                ->where('bahan_bakus.nama_bahan', 'Ayam Potong')
                ->select('produksi_details.*')
                ->first();
            
            echo "<div class='code'>";
            echo "<strong>After Fix:</strong><br>";
            echo "Production Detail: {$verifyDetail->qty_resep} {$verifyDetail->satuan_resep}<br>";
            echo "Stock Movement: {$verifyMovement->qty_as_input} {$verifyMovement->satuan_as_input}";
            echo "</div>";
            
            if ($verifyDetail->qty_resep == 160 && $verifyDetail->satuan_resep == 'Potong' &&
                $verifyMovement->qty_as_input == 160 && $verifyMovement->satuan_as_input == 'Potong') {
                
                echo "<h2 class='success'>🎉 SUCCESS! FIX APPLIED!</h2>";
                echo "<p class='success'>Both production detail and stock movement now show 160 Potong!</p>";
                
            } else {
                echo "<h2 class='error'>❌ FIX FAILED!</h2>";
                echo "<p class='error'>Data is still incorrect. Check database manually.</p>";
            }
            
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<h2>🧪 TEST NOW</h2>";
            echo "<p><strong>Refresh these pages to see the fix:</strong></p>";
            echo "<ul>";
            echo "<li><a href='/laporan/stok?tipe=material&item_id=2&satuan_id=22' target='_blank'>Laporan Stok - Ayam Potong (Potong)</a> → Should show <strong>160 Potong</strong></li>";
            echo "<li><a href='/laporan/stok?tipe=material&item_id=2&satuan_id=2' target='_blank'>Laporan Stok - Ayam Potong (Kilogram)</a> → Should show <strong>53.33 Kg</strong></li>";
            echo "<li><a href='/transaksi/produksi/2' target='_blank'>Transaksi Produksi ID 2</a> → Should show <strong>160 Potong</strong></li>";
            echo "</ul>";
            
            echo "<p class='info'><strong>Note:</strong> Jika masih menunjukkan 120 Potong, tekan Ctrl+F5 untuk hard refresh!</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='section'>";
            echo "<h2 class='error'>❌ ERROR</h2>";
            echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
            echo "<p>File: " . $e->getFile() . "</p>";
            echo "<p>Line: " . $e->getLine() . "</p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <title>FIX AYAM POTONG ID=1 (120→160 POTONG)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
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
        <h1 style="color: red;">🔥 FIX AYAM POTONG ID=1: 120→160 POTONG</h1>
        <p><strong>URL yang benar:</strong> laporan/stok?tipe=material&item_id=<strong>1</strong>&satuan_id=</p>
        
        <?php
        try {
            require_once '../vendor/autoload.php';
            $app = require_once '../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            echo "<div class='section'>";
            echo "<h2>🔍 CHECKING AYAM POTONG ID=1</h2>";
            
            // Check which item is ID=1
            $item = DB::table('bahan_bakus')->where('id', 1)->first();
            if ($item) {
                echo "<div class='code'>";
                echo "<strong>Item ID=1:</strong><br>";
                echo "Nama: {$item->nama_bahan}<br>";
                echo "Satuan ID: {$item->satuan_id}<br>";
                echo "Saldo Awal: " . ($item->saldo_awal ?? 'N/A');
                echo "</div>";
            }
            
            // Check stock movements for item_id=1
            $stockMovements = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 1) // CORRECT ID
                ->where('ref_type', 'production')
                ->get();
            
            echo "<p><strong>Stock Movements for Item ID=1:</strong></p>";
            foreach ($stockMovements as $movement) {
                echo "<div class='code'>";
                echo "Movement ID: {$movement->id}<br>";
                echo "Qty: {$movement->qty}<br>";
                echo "Qty as Input: " . ($movement->qty_as_input ?? 'NULL') . "<br>";
                echo "Satuan as Input: " . ($movement->satuan_as_input ?? 'NULL') . "<br>";
                echo "Ref ID: {$movement->ref_id}<br>";
                echo "Date: {$movement->tanggal}";
                echo "</div>";
            }
            
            // Check production details for this item
            $prodDetails = DB::table('produksi_details')
                ->where('bahan_baku_id', 1) // CORRECT ID
                ->get();
            
            echo "<p><strong>Production Details for Item ID=1:</strong></p>";
            foreach ($prodDetails as $detail) {
                echo "<div class='code'>";
                echo "Detail ID: {$detail->id}<br>";
                echo "Produksi ID: {$detail->produksi_id}<br>";
                echo "Qty Resep: {$detail->qty_resep}<br>";
                echo "Satuan Resep: {$detail->satuan_resep}<br>";
                echo "Qty Konversi: {$detail->qty_konversi}";
                echo "</div>";
            }
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<h2>🔧 APPLYING FIX FOR ID=1</h2>";
            
            // Update production details to 160 Potong
            if ($prodDetails->count() > 0) {
                foreach ($prodDetails as $detail) {
                    echo "<p class='warning'>Updating production detail ID {$detail->id} to 160 Potong...</p>";
                    
                    $updateDetail = DB::table('produksi_details')
                        ->where('id', $detail->id)
                        ->update([
                            'qty_resep' => 160,
                            'satuan_resep' => 'Potong'
                        ]);
                    
                    if ($updateDetail) {
                        echo "<p class='success'>✅ Production detail {$detail->id} updated!</p>";
                    } else {
                        echo "<p class='error'>❌ Failed to update production detail {$detail->id}</p>";
                    }
                }
            }
            
            // Update stock movements to 160 Potong
            if ($stockMovements->count() > 0) {
                foreach ($stockMovements as $movement) {
                    echo "<p class='warning'>Updating stock movement ID {$movement->id} to 160 Potong...</p>";
                    
                    $updateMovement = DB::table('stock_movements')
                        ->where('id', $movement->id)
                        ->update([
                            'qty_as_input' => 160,
                            'satuan_as_input' => 'Potong'
                        ]);
                    
                    if ($updateMovement) {
                        echo "<p class='success'>✅ Stock movement {$movement->id} updated!</p>";
                    } else {
                        echo "<p class='error'>❌ Failed to update stock movement {$movement->id}</p>";
                    }
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
                echo "<p class='warning'>⚠️ Cache clear: " . $e->getMessage() . "</p>";
            }
            
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<h2>✅ VERIFICATION</h2>";
            
            // Verify the changes
            $verifyDetails = DB::table('produksi_details')
                ->where('bahan_baku_id', 1)
                ->get();
            
            $verifyMovements = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 1)
                ->where('ref_type', 'production')
                ->get();
            
            echo "<p><strong>After Fix - Production Details:</strong></p>";
            foreach ($verifyDetails as $detail) {
                echo "<div class='code'>";
                echo "Detail ID {$detail->id}: {$detail->qty_resep} {$detail->satuan_resep}";
                echo "</div>";
            }
            
            echo "<p><strong>After Fix - Stock Movements:</strong></p>";
            foreach ($verifyMovements as $movement) {
                echo "<div class='code'>";
                echo "Movement ID {$movement->id}: {$movement->qty_as_input} {$movement->satuan_as_input}";
                echo "</div>";
            }
            
            // Check if all are 160 Potong
            $allCorrect = true;
            foreach ($verifyDetails as $detail) {
                if ($detail->qty_resep != 160 || $detail->satuan_resep != 'Potong') {
                    $allCorrect = false;
                    break;
                }
            }
            foreach ($verifyMovements as $movement) {
                if ($movement->qty_as_input != 160 || $movement->satuan_as_input != 'Potong') {
                    $allCorrect = false;
                    break;
                }
            }
            
            if ($allCorrect) {
                echo "<h2 class='success'>🎉 SUCCESS! ALL DATA FIXED TO 160 POTONG!</h2>";
            } else {
                echo "<h2 class='error'>❌ SOME DATA STILL INCORRECT!</h2>";
            }
            
            echo "</div>";
            
            echo "<div class='section'>";
            echo "<h2>🧪 TEST THE FIX</h2>";
            echo "<p><strong>Refresh these pages (Ctrl+F5):</strong></p>";
            echo "<ul>";
            echo "<li><a href='/laporan/stok?tipe=material&item_id=1&satuan_id=22' target='_blank'>Laporan Stok - ID=1 (Potong)</a> → Should show <strong>160 Potong</strong></li>";
            echo "<li><a href='/laporan/stok?tipe=material&item_id=1&satuan_id=2' target='_blank'>Laporan Stok - ID=1 (Kilogram)</a> → Should show <strong>53.33 Kg</strong></li>";
            echo "<li><a href='/laporan/stok?tipe=material&item_id=1' target='_blank'>Laporan Stok - ID=1 (All Units)</a> → Check all conversions</li>";
            echo "</ul>";
            
            echo "<p class='info'><strong>IMPORTANT:</strong> Tekan Ctrl+F5 untuk hard refresh agar cache browser juga ter-refresh!</p>";
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
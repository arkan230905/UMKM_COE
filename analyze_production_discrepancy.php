<!DOCTYPE html>
<html>
<head>
    <title>Analyze Production Discrepancy</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Analyze Production Discrepancy: 160 vs 120 Potong</h1>
    
    <?php
    try {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        echo "<div class='section'>";
        echo "<h2>1. BOM Data for Ayam Potong</h2>";
        
        // Check BOM data
        $bomData = DB::table('bom_job_bbbs')
            ->join('bom_job_costings', 'bom_job_bbbs.bom_job_costing_id', '=', 'bom_job_costings.id')
            ->join('bahan_bakus', 'bom_job_bbbs.bahan_baku_id', '=', 'bahan_bakus.id')
            ->join('produks', 'bom_job_costings.produk_id', '=', 'produks.id')
            ->where('bahan_bakus.nama_bahan', 'Ayam Potong')
            ->select('bom_job_bbbs.*', 'bahan_bakus.nama_bahan', 'produks.nama_produk', 'bom_job_costings.produk_id')
            ->get();
        
        foreach ($bomData as $bom) {
            echo "<p><strong>Produk:</strong> {$bom->nama_produk}</p>";
            echo "<p><strong>Jumlah per unit:</strong> {$bom->jumlah} {$bom->satuan}</p>";
            echo "<p><strong>Subtotal:</strong> Rp " . number_format($bom->subtotal) . "</p>";
        }
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2>2. Production Records</h2>";
        
        // Check production records that used Ayam Potong
        $productions = DB::table('produksis')
            ->join('produksi_details', 'produksis.id', '=', 'produksi_details.produksi_id')
            ->join('bahan_bakus', 'produksi_details.bahan_baku_id', '=', 'bahan_bakus.id')
            ->where('bahan_bakus.nama_bahan', 'Ayam Potong')
            ->select('produksis.*', 'produksi_details.qty_resep', 'produksi_details.satuan_resep', 'produksi_details.qty_konversi')
            ->get();
        
        foreach ($productions as $prod) {
            echo "<p><strong>Production ID:</strong> {$prod->id}</p>";
            echo "<p><strong>Qty Produksi:</strong> {$prod->qty_produksi} unit</p>";
            echo "<p><strong>Qty Resep:</strong> {$prod->qty_resep} {$prod->satuan_resep}</p>";
            echo "<p><strong>Qty Konversi:</strong> {$prod->qty_konversi}</p>";
            
            // Calculate expected usage
            if ($bomData->count() > 0) {
                $bomQtyPerUnit = $bomData->first()->jumlah;
                $expectedUsage = $bomQtyPerUnit * $prod->qty_produksi;
                echo "<p class='info'><strong>Expected Usage:</strong> {$bomQtyPerUnit} × {$prod->qty_produksi} = {$expectedUsage} Potong</p>";
                
                if ($prod->qty_resep != $expectedUsage) {
                    echo "<p class='error'>❌ MISMATCH: BOM says {$expectedUsage} but production used {$prod->qty_resep}</p>";
                } else {
                    echo "<p class='success'>✅ MATCH: BOM and production usage align</p>";
                }
            }
        }
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2>3. Stock Movement Records</h2>";
        
        // Check stock movements for production
        $stockMovements = DB::table('stock_movements')
            ->where('item_type', 'material')
            ->where('item_id', 2) // Ayam Potong ID
            ->where('ref_type', 'production')
            ->get();
        
        foreach ($stockMovements as $movement) {
            echo "<p><strong>Date:</strong> {$movement->tanggal}</p>";
            echo "<p><strong>Qty:</strong> {$movement->qty} (base unit)</p>";
            echo "<p><strong>Qty as Input:</strong> " . ($movement->qty_as_input ?? 'null') . "</p>";
            echo "<p><strong>Satuan as Input:</strong> " . ($movement->satuan_as_input ?? 'null') . "</p>";
            echo "<p><strong>Direction:</strong> {$movement->direction}</p>";
        }
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2>4. Analysis & Recommendation</h2>";
        
        if ($productions->count() > 0 && $bomData->count() > 0) {
            $prod = $productions->first();
            $bom = $bomData->first();
            
            $bomExpected = $bom->jumlah * $prod->qty_produksi;
            $actualUsed = $prod->qty_resep;
            
            echo "<p><strong>BOM Expected:</strong> {$bom->jumlah} × {$prod->qty_produksi} = {$bomExpected} Potong</p>";
            echo "<p><strong>Actually Used:</strong> {$actualUsed} Potong</p>";
            
            if ($bomExpected != $actualUsed) {
                echo "<p class='error'><strong>PROBLEM FOUND:</strong></p>";
                echo "<ul>";
                echo "<li>BOM says should use: {$bomExpected} Potong</li>";
                echo "<li>Production actually used: {$actualUsed} Potong</li>";
                echo "<li>Difference: " . ($actualUsed - $bomExpected) . " Potong</li>";
                echo "</ul>";
                
                echo "<p><strong>SOLUTION:</strong></p>";
                echo "<ul>";
                echo "<li>Either update BOM to reflect actual usage: {$actualUsed} Potong per {$prod->qty_produksi} unit</li>";
                echo "<li>Or correct the production record to match BOM: {$bomExpected} Potong</li>";
                echo "</ul>";
            } else {
                echo "<p class='success'>✅ No discrepancy found between BOM and production usage</p>";
            }
        }
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<h2>Error</h2>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "<p>File: " . $e->getFile() . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
        echo "</div>";
    }
    ?>
</body>
</html>
<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging edit totals calculation...\n\n";

try {
    // Get Ayam Ketumbar data
    $ayamKetumbar = \App\Models\Produk::where('nama_produk', 'LIKE', '%Ayam Ketumbar%')->first();
    
    if (!$ayamKetumbar) {
        echo "❌ Ayam Ketumbar not found!\n";
        exit(1);
    }
    
    echo "=== AYAM KETUMBAR DATA ===\n";
    echo sprintf("ID: %d\n", $ayamKetumbar->id);
    echo sprintf("Nama: %s\n", $ayamKetumbar->nama_produk);
    echo sprintf("Harga BOM: Rp %s\n", number_format($ayamKetumbar->harga_bom, 2));
    
    // Get BOM data
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $ayamKetumbar->id)->first();
    
    if ($bomJobCosting) {
        echo "\n=== BOM JOB COSTING DATA ===\n";
        echo sprintf("ID: %d\n", $bomJobCosting->id);
        echo sprintf("Total BBB: Rp %s\n", number_format($bomJobCosting->total_bbb, 2));
        echo sprintf("Total Bahan Pendukung: Rp %s\n", number_format($bomJobCosting->total_bahan_pendukung, 2));
        
        // Get BBB details
        $bbbDetails = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
        echo "\n=== BBB DETAILS ===\n";
        foreach ($bbbDetails as $detail) {
            echo sprintf("ID: %d | Bahan ID: %d | Jumlah: %s | Satuan: %s | Harga: %s | Subtotal: %s\n", 
                $detail->id,
                $detail->bahan_baku_id,
                $detail->jumlah,
                $detail->satuan,
                $detail->harga_satuan,
                $detail->subtotal
            );
        }
        
        // Get Bahan Pendukung details
        $pendukungDetails = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->get();
        echo "\n=== BAHAN PENDUKUNG DETAILS ===\n";
        foreach ($pendukungDetails as $detail) {
            echo sprintf("ID: %d | Bahan ID: %d | Jumlah: %s | Satuan: %s | Harga: %s | Subtotal: %s\n", 
                $detail->id,
                $detail->bahan_pendukung_id,
                $detail->jumlah,
                $detail->satuan,
                $detail->harga_satuan,
                $detail->subtotal
            );
        }
        
        // Calculate totals from database
        $totalBBBFromDB = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->sum('subtotal');
        $totalPendukungFromDB = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->sum('subtotal');
        
        echo "\n=== CALCULATED TOTALS FROM DATABASE ===\n";
        echo sprintf("BBB Total: Rp %s\n", number_format($totalBBBFromDB, 2));
        echo sprintf("Bahan Pendukung Total: Rp %s\n", number_format($totalPendukungFromDB, 2));
        echo sprintf("Grand Total: Rp %s\n", number_format($totalBBBFromDB + $totalPendukungFromDB, 2));
        
        // Check if there's a mismatch
        echo "\n=== MISMATCH CHECK ===\n";
        if ($bomJobCosting->total_bbb != $totalBBBFromDB) {
            echo sprintf("❌ BBB MISMATCH! BomJobCosting: %s vs DB: %s\n", 
                number_format($bomJobCosting->total_bbb, 2),
                number_format($totalBBBFromDB, 2)
            );
            
            // Fix BomJobCosting
            \DB::table('bom_job_costings')
                ->where('id', $bomJobCosting->id)
                ->update(['total_bbb' => $totalBBBFromDB]);
            
            echo "✅ Fixed BBB total in BomJobCosting\n";
        }
        
        if ($bomJobCosting->total_bahan_pendukung != $totalPendukungFromDB) {
            echo sprintf("❌ Bahan Pendukung MISMATCH! BomJobCosting: %s vs DB: %s\n", 
                number_format($bomJobCosting->total_bahan_pendukung, 2),
                number_format($totalPendukungFromDB, 2)
            );
            
            // Fix BomJobCosting
            \DB::table('bom_job_costings')
                ->where('id', $bomJobCosting->id)
                ->update(['total_bahan_pendukung' => $totalPendukungFromDB]);
            
            echo "✅ Fixed Bahan Pendukung total in BomJobCosting\n";
        }
        
        // Recalculate and update produk
        $newTotalHPP = $totalBBBFromDB + $totalPendukungFromDB;
        if ($ayamKetumbar->harga_bom != $newTotalHPP) {
            echo sprintf("❌ Produk Harga BOM MISMATCH! Current: %s vs Calculated: %s\n", 
                number_format($ayamKetumbar->harga_bom, 2),
                number_format($newTotalHPP, 2)
            );
            
            // Update produk
            \DB::table('produks')
                ->where('id', $ayamKetumbar->id)
                ->update([
                    'harga_bom' => $newTotalHPP,
                    'harga_pokok' => $newTotalHPP
                ]);
            
            echo "✅ Fixed Produk harga_bom\n";
        }
        
    } else {
        echo "❌ No BOM Job Costing found!\n";
    }
    
    echo "\n=== JAVASCRIPT DEBUGGING HELP ===\n";
    echo "Add this to browser console on edit page:\n";
    echo "// Check existing rows\n";
    echo "console.log('BB Rows:', document.querySelectorAll('#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)').length);\n";
    echo "console.log('BP Rows:', document.querySelectorAll('#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)').length);\n";
    echo "\n";
    echo "// Check subtotal displays\n";
    echo "document.querySelectorAll('.subtotal-display').forEach((el, i) => {\n";
    echo "    console.log(`Row ${i}: ${el.textContent}`);\n";
    echo "});\n";
    echo "\n";
    echo "// Manual calculate totals\n";
    echo "let bbTotal = 0;\n";
    echo "document.querySelectorAll('#bahanBakuTable .subtotal-display').forEach(el => {\n";
    echo "    const text = el.textContent.replace(/[Rp\\s.]/g, '').replace(',', '.');\n";
    echo "    bbTotal += parseFloat(text) || 0;\n";
    echo "});\n";
    echo "console.log('Manual BB Total:', bbTotal);\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

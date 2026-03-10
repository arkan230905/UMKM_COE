<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking BomJobBahanPendukung table structure and data...\n\n";

try {
    // Check table structure
    echo "=== TABLE STRUCTURE ===\n";
    $columns = \DB::select("DESCRIBE bom_job_bahan_pendukungs");
    foreach ($columns as $column) {
        echo sprintf("%-20s %-20s %-10s %-10s %-10s\n", 
            $column->Field, 
            $column->Type, 
            $column->Null, 
            $column->Key, 
            $column->Default
        );
    }
    
    echo "\n=== ALL BAHAN PENDUKUNG RECORDS ===\n";
    $records = \DB::table('bom_job_bahan_pendukungs')->get();
    
    foreach ($records as $record) {
        echo sprintf("ID: %d | Bahan ID: %d | Jumlah: %s | Satuan: %s | Harga: %s | Subtotal: %s\n", 
            $record->id,
            $record->bahan_pendukung_id,
            $record->jumlah,
            $record->satuan,
            $record->harga_satuan,
            $record->subtotal
        );
    }
    
    echo "\n=== CHECKING SPECIFIC MINYAK GORENG ISSUE ===\n";
    
    // Get the problematic record
    $minyakRecord = \DB::table('bom_job_bahan_pendukungs')
        ->where('bahan_pendukung_id', 2) // Minyak Goreng ID
        ->first();
    
    if ($minyakRecord) {
        echo "Found Minyak Goreng record:\n";
        echo sprintf("  Jumlah: %s (type: %s)\n", $minyakRecord->jumlah, gettype($minyakRecord->jumlah));
        echo sprintf("  Harga Satuan: %s (type: %s)\n", $minyakRecord->harga_satuan, gettype($minyakRecord->harga_satuan));
        echo sprintf("  Subtotal: %s (type: %s)\n", $minyakRecord->subtotal, gettype($minyakRecord->subtotal));
        
        // Manual calculation
        $manualSubtotal = (float)$minyakRecord->jumlah * (float)$minyakRecord->harga_satuan;
        echo sprintf("  Manual calculation: %s * %s = %s\n", 
            $minyakRecord->jumlah, 
            $minyakRecord->harga_satuan, 
            $manualSubtotal
        );
        
        // Check if the issue is with decimal storage
        echo "\n=== TESTING DECIMAL STORAGE ===\n";
        
        // Test storing 700.00
        \DB::table('bom_job_bahan_pendukungs')
            ->where('id', $minyakRecord->id)
            ->update(['subtotal' => 700.00]);
        
        $updatedRecord = \DB::table('bom_job_bahan_pendukungs')->where('id', $minyakRecord->id)->first();
        echo sprintf("After update to 700.00: %s\n", $updatedRecord->subtotal);
        
        // Test storing as integer
        \DB::table('bom_job_bahan_pendukungs')
            ->where('id', $minyakRecord->id)
            ->update(['subtotal' => 700]);
        
        $updatedRecord2 = \DB::table('bom_job_bahan_pendukungs')->where('id', $minyakRecord->id)->first();
        echo sprintf("After update to 700: %s\n", $updatedRecord2->subtotal);
        
        // Fix the record with correct calculation
        $correctSubtotal = 50 * 14; // 50ml x Rp 14/ml = 700
        \DB::table('bom_job_bahan_pendukungs')
            ->where('id', $minyakRecord->id)
            ->update(['subtotal' => $correctSubtotal]);
        
        echo sprintf("Fixed to correct value: %s\n", $correctSubtotal);
        
    } else {
        echo "❌ Minyak Goreng record not found\n";
    }
    
    echo "\n=== CHECKING ALL SUBTOTAL CALCULATIONS ===\n";
    $allRecords = \DB::table('bom_job_bahan_pendukungs')->get();
    
    foreach ($allRecords as $record) {
        $expectedSubtotal = (float)$record->jumlah * (float)$record->harga_satuan;
        $actualSubtotal = (float)$record->subtotal;
        $difference = abs($expectedSubtotal - $actualSubtotal);
        
        if ($difference > 0.01) { // Allow small floating point differences
            echo sprintf("MISMATCH - ID: %d | Expected: %s | Actual: %s | Diff: %s\n", 
                $record->id, 
                number_format($expectedSubtotal, 2), 
                number_format($actualSubtotal, 2), 
                number_format($difference, 2)
            );
            
            // Fix this record
            \DB::table('bom_job_bahan_pendukungs')
                ->where('id', $record->id)
                ->update(['subtotal' => $expectedSubtotal]);
        }
    }
    
    echo "\n=== RECALCULATING BOM TOTALS ===\n";
    
    // Get Ayam Ketumbar BOM
    $ayamKetumbar = \App\Models\Produk::where('nama_produk', 'LIKE', '%Ayam Ketumbar%')->first();
    if ($ayamKetumbar) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $ayamKetumbar->id)->first();
        if ($bomJobCosting) {
            // Recalculate totals
            $totalBBB = \DB::table('bom_job_b_b_s')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->sum('subtotal');
                
            $totalPendukung = \DB::table('bom_job_bahan_pendukungs')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->sum('subtotal');
            
            echo sprintf("Recalculated totals:\n");
            echo sprintf("  BBB: Rp %s\n", number_format($totalBBB, 2));
            echo sprintf("  Bahan Pendukung: Rp %s\n", number_format($totalPendukung, 2));
            echo sprintf("  Total: Rp %s\n", number_format($totalBBB + $totalPendukung, 2));
            
            // Update BomJobCosting
            \DB::table('bom_job_costings')
                ->where('id', $bomJobCosting->id)
                ->update([
                    'total_bbb' => $totalBBB,
                    'total_bahan_pendukung' => $totalPendukung
                ]);
            
            // Update produk
            \DB::table('produks')
                ->where('id', $ayamKetumbar->id)
                ->update([
                    'harga_bom' => $totalBBB + $totalPendukung,
                    'harga_pokok' => $totalBBB + $totalPendukung
                ]);
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

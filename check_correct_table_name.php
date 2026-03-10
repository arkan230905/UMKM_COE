<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking correct table names...\n\n";

try {
    // Get all tables
    $tables = \DB::select("SHOW TABLES");
    echo "=== ALL TABLES ===\n";
    
    $bahanPendukungTables = [];
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        echo sprintf("%s\n", $tableName);
        
        if (stripos($tableName, 'bahan_pendukung') !== false || stripos($tableName, 'bom') !== false) {
            $bahanPendukungTables[] = $tableName;
        }
    }
    
    echo "\n=== BAHAN PENDUKUNG RELATED TABLES ===\n";
    foreach ($bahanPendukungTables as $table) {
        echo sprintf("%s\n", $table);
    }
    
    // Check the correct table
    $correctTable = 'bom_job_bahan_pendukung'; // singular, not plural
    if (\Schema::hasTable($correctTable)) {
        echo "\n=== CHECKING TABLE: $correctTable ===\n";
        
        $columns = \DB::select("DESCRIBE $correctTable");
        foreach ($columns as $column) {
            echo sprintf("%-20s %-20s %-10s %-10s %-10s\n", 
                $column->Field, 
                $column->Type, 
                $column->Null, 
                $column->Key, 
                $column->Default
            );
        }
        
        echo "\n=== ALL RECORDS ===\n";
        $records = \DB::table($correctTable)->get();
        
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
        
        echo "\n=== CHECKING MINYAK GORENG ISSUE ===\n";
        
        $minyakRecord = \DB::table($correctTable)
            ->where('bahan_pendukung_id', 2)
            ->first();
        
        if ($minyakRecord) {
            echo "Found Minyak Goreng record:\n";
            echo sprintf("  Jumlah: %s\n", $minyakRecord->jumlah);
            echo sprintf("  Harga Satuan: %s\n", $minyakRecord->harga_satuan);
            echo sprintf("  Subtotal: %s\n", $minyakRecord->subtotal);
            
            $expectedSubtotal = (float)$minyakRecord->jumlah * (float)$minyakRecord->harga_satuan;
            echo sprintf("  Expected: %s\n", $expectedSubtotal);
            
            if (abs($expectedSubtotal - (float)$minyakRecord->subtotal) > 0.01) {
                echo "  ❌ MISMATCH FOUND! Fixing...\n";
                
                // Fix the record
                \DB::table($correctTable)
                    ->where('id', $minyakRecord->id)
                    ->update(['subtotal' => $expectedSubtotal]);
                
                echo "  ✅ Fixed to: $expectedSubtotal\n";
            } else {
                echo "  ✅ Subtotal is correct\n";
            }
        }
        
    } else {
        echo "\n❌ Table $correctTable not found!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

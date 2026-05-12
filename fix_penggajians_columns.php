<?php
/**
 * Script to fix missing columns in penggajians table
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Checking penggajians table structure...\n";
    
    // Check if columns exist and add them if missing
    $columnsToAdd = [
        'tunjangan_jabatan' => 'decimal(15,2)',
        'tunjangan_transport' => 'decimal(15,2)', 
        'tunjangan_konsumsi' => 'decimal(15,2)',
        'total_tunjangan' => 'decimal(15,2)'
    ];
    
    foreach ($columnsToAdd as $columnName => $columnType) {
        if (!Schema::hasColumn('penggajians', $columnName)) {
            echo "Adding column: {$columnName}\n";
            
            Schema::table('penggajians', function (Blueprint $table) use ($columnName) {
                $table->decimal($columnName, 15, 2)->default(0)->after('tunjangan');
            });
            
            echo "✅ Column {$columnName} added successfully\n";
        } else {
            echo "✅ Column {$columnName} already exists\n";
        }
    }
    
    // Show current table structure
    echo "\nCurrent penggajians table columns:\n";
    $columns = DB::select("SHOW COLUMNS FROM penggajians");
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    echo "\n✅ Penggajians table structure fixed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
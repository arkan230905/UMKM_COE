<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Database Tables ===\n";

// Check available tables
$tables = \DB::select('SHOW TABLES');
echo "Tables related to pembelian:\n";
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (strpos($tableName, 'pembelian') !== false) {
        echo "- {$tableName}\n";
    }
}

// Check pembelian model relationships
echo "\nChecking Pembelian model:\n";
$pembelian = \App\Models\Pembelian::find(2);
if ($pembelian) {
    echo "Pembelian found: #{$pembelian->id}\n";
    
    // Try to get details using different possible table names
    try {
        $details = $pembelian->details;
        echo "Details count: " . $details->count() . "\n";
        
        foreach ($details as $detail) {
            echo "Detail: bahan_baku_id={$detail->bahan_baku_id}, jumlah={$detail->jumlah}\n";
        }
    } catch (Exception $e) {
        echo "Error getting details: " . $e->getMessage() . "\n";
    }
}

// Check if there's a different table name
echo "\nChecking for alternative table names:\n";
$possibleTables = ['pembelian_details', 'detail_pembelian', 'pembelian_detail'];
foreach ($possibleTables as $tableName) {
    try {
        $count = \DB::table($tableName)->count();
        echo "- {$tableName}: {$count} records\n";
    } catch (Exception $e) {
        echo "- {$tableName}: doesn't exist\n";
    }
}
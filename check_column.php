<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

try {
    // Get database name from config
    $dbName = config('database.connections.mysql.database');
    echo "Database name: $dbName\n";
    
    // Check if nomor_penjualan column exists
    $result = DB::select("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = ? AND table_name = 'penjualans' AND column_name = 'nomor_penjualan'", [$dbName]);
    
    $hasColumn = $result[0]->count > 0;
    echo "Has 'nomor_penjualan' column: " . ($hasColumn ? 'YES' : 'NO') . "\n";
    
    if ($hasColumn) {
        // Get some sample data
        $data = DB::table('penjualans')->select('id', 'nomor_penjualan', 'tanggal')->limit(3)->get();
        
        echo "\nSample data:\n";
        foreach ($data as $row) {
            echo "ID: {$row->id}, Nomor: " . ($row->nomor_penjualan ?? 'NULL') . ", Tanggal: {$row->tanggal}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

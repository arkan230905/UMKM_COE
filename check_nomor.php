<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\Schema;

try {
    $hasColumn = Schema::hasColumn('penjualans', 'nomor_penjualan');
    echo "Has column 'nomor_penjualan': " . ($hasColumn ? 'YES' : 'NO') . "\n";
    
    if ($hasColumn) {
        // Cek beberapa data penjualan
        $penjualan = \DB::table('penjualans')->limit(3)->get(['id', 'nomor_penjualan', 'tanggal']);
        echo "\nSample penjualan data:\n";
        foreach ($penjualan as $p) {
            echo "ID: {$p->id}, Nomor: " . ($p->nomor_penjualan ?? 'NULL') . ", Tanggal: {$p->tanggal}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

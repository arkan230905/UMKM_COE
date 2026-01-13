<?php

// Check nomor_pembelian column
$result = DB::select("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pembelians' AND column_name = 'nomor_pembelian'");

$hasColumn = $result[0]->count > 0;
echo "Has 'nomor_pembelian' column: " . ($hasColumn ? 'YES' : 'NO') . "\n";

if ($hasColumn) {
    // Get some sample data
    $data = DB::table('pembelians')->select('id', 'nomor_pembelian', 'tanggal')->limit(5)->get();
    
    echo "\nSample data:\n";
    foreach ($data as $row) {
        echo "ID: {$row->id}, Nomor: " . ($row->nomor_pembelian ?? 'NULL') . ", Tanggal: {$row->tanggal}\n";
    }
}

<?php

// Check pembelian table structure
$columns = DB::select("SHOW COLUMNS FROM pembelians");
echo "Struktur Tabel Pembelians:\n";
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

// Check sample data
echo "\nSample Data:\n";
$data = DB::table('pembelians')->limit(3)->get();
foreach ($data as $row) {
    echo "ID: {$row->id}, Tanggal: {$row->tanggal}, Total: {$row->total}\n";
}

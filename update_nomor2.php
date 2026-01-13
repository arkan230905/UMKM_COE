<?php
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Update nomor penjualan yang kosong
$penjualan = DB::table('penjualans')
    ->whereNull('nomor_penjualan')
    ->get(['id', 'tanggal']);

echo "Found " . $penjualan->count() . " penjualan records without nomor_penjualan\n";

if ($penjualan->count() > 0) {
    foreach ($penjualan as $p) {
        $tanggal = $p->tanggal;
        $date = is_string($tanggal) ? $tanggal : $tanggal->format('Ymd');
        
        // Hitung jumlah penjualan hari ini
        $count = DB::table('penjualans')
            ->whereDate('tanggal', $tanggal)
            ->whereNotNull('nomor_penjualan')
            ->count() + 1;
        
        // Format: PJ-YYYYMMDD-0001
        $nomor = 'PJ-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Update the record
        DB::table('penjualan')
            ->where('id', $p->id)
            ->update(['nomor_penjualan' => $nomor]);
        
        echo "Updated ID {$p->id} with nomor: $nomor\n";
    }
    
    echo "\nUpdate completed!\n";
}

// Show updated data
$updated = DB::table('penjualan')
    ->select('id', 'nomor_penjualan', 'tanggal')
    ->limit(5)
    ->get();

echo "\nUpdated sample data:\n";
foreach ($updated as $row) {
    echo "ID: {$row->id}, Nomor: " . ($row->nomor_penjualan ?? 'NULL') . ", Tanggal: {$row->tanggal}\n";
}

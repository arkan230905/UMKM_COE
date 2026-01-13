<?php

// Update nomor pembelian yang kosong
$records = DB::table('pembelians')
    ->whereNull('nomor_pembelian')
    ->get(['id', 'tanggal']);

echo "Found " . $records->count() . " pembelian records without nomor_pembelian\n";

if ($records->count() > 0) {
    foreach ($records as $record) {
        $tanggal = $record->tanggal;
        $date = is_string($tanggal) ? $tanggal : $tanggal->format('Ymd');
        
        // Hitung jumlah pembelian hari ini
        $count = DB::table('pembelians')
            ->whereDate('tanggal', $tanggal)
            ->whereNotNull('nomor_pembelian')
            ->count() + 1;
        
        // Format: PB-YYYYMMDD-0001
        $nomor = 'PB-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Update the record
        DB::table('pembelians')
            ->where('id', $record->id)
            ->update(['nomor_pembelian' => $nomor]);
        
        echo "Updated ID {$record->id} with nomor: $nomor\n";
    }
    
    echo "\nUpdate completed!\n";
}

// Show updated data
$updated = DB::table('pembelians')
    ->select('id', 'nomor_pembelian', 'tanggal')
    ->limit(10)
    ->get();

echo "\nUpdated sample data:\n";
foreach ($updated as $row) {
    echo "ID: {$row->id}, Nomor: " . ($row->nomor_pembelian ?? 'NULL') . ", Tanggal: {$row->tanggal}\n";
}

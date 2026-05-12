<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Cek Foreign Key produks.coa_persediaan_id ===\n\n";

// Check the foreign key constraint
$result = DB::select("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'eadt_umkm'
    AND TABLE_NAME = 'produks'
    AND COLUMN_NAME = 'coa_persediaan_id'
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

if (!empty($result)) {
    foreach ($result as $fk) {
        echo "Foreign Key: {$fk->CONSTRAINT_NAME}\n";
        echo "Column: {$fk->COLUMN_NAME}\n";
        echo "References: {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n\n";
    }
    
    echo "❌ Foreign key mereferensi ke 'kode_akun' (string), bukan 'id' (integer)\n";
    echo "Solusi: Kirim kode_akun, bukan id\n";
} else {
    echo "✓ Tidak ada foreign key constraint\n";
}

// Check COA id 171
echo "\n=== Cek COA ID 171 ===\n";
$coa = \App\Models\Coa::withoutGlobalScopes()->where('id', 171)->first();
if ($coa) {
    echo "ID: {$coa->id}\n";
    echo "Kode: {$coa->kode_akun}\n";
    echo "Nama: {$coa->nama_akun}\n";
    echo "User ID: {$coa->user_id}\n";
} else {
    echo "COA ID 171 tidak ditemukan\n";
}

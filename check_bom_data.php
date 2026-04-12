<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING BOM DATA ===\n";

// Check bom_job_bahan_pendukung
$bomBahanPendukung = DB::table('bom_job_bahan_pendukung')->get();
echo "BOM Bahan Pendukung records: " . $bomBahanPendukung->count() . "\n\n";

foreach($bomBahanPendukung as $bom) {
    $bahanPendukung = DB::table('bahan_pendukungs')->where('id', $bom->bahan_pendukung_id)->first();
    echo "ID: {$bom->id}\n";
    echo "Bahan: " . ($bahanPendukung->nama_bahan ?? 'N/A') . "\n";
    echo "Jumlah: {$bom->jumlah}\n";
    echo "Satuan: {$bom->satuan}\n";
    echo "Harga Satuan: {$bom->harga_satuan}\n";
    echo "Subtotal: {$bom->subtotal}\n";
    echo "---\n";
}

// Check bom_job_bbb
echo "\n=== BOM BAHAN BAKU ===\n";
$bomBahanBaku = DB::table('bom_job_bbb')->get();
echo "BOM Bahan Baku records: " . $bomBahanBaku->count() . "\n\n";

foreach($bomBahanBaku as $bom) {
    $bahanBaku = DB::table('bahan_bakus')->where('id', $bom->bahan_baku_id)->first();
    echo "ID: {$bom->id}\n";
    echo "Bahan: " . ($bahanBaku->nama_bahan ?? 'N/A') . "\n";
    echo "Jumlah: {$bom->jumlah}\n";
    echo "Satuan: {$bom->satuan}\n";
    echo "Harga Satuan: {$bom->harga_satuan}\n";
    echo "Subtotal: {$bom->subtotal}\n";
    echo "---\n";
}
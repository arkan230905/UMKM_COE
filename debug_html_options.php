<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Simulate auth user
auth()->loginUsingId(3);

$bahanBakus = \App\Models\BahanBaku::where('user_id', 3)->with('satuan')->get();

echo "=== BAHAN BAKU HTML OPTIONS ===\n\n";

foreach ($bahanBakus as $bb) {
    $coa = $bb->coa_persediaan_id 
        ? \App\Models\Coa::where('kode_akun', $bb->coa_persediaan_id)
            ->where('user_id', auth()->id())
            ->first() 
        : null;
    
    $coaKode = $coa ? $coa->kode_akun : '114';
    $coaNama = $coa ? $coa->nama_akun : 'Persediaan Bahan Baku';
    
    echo "Item: {$bb->nama_bahan}\n";
    echo "  coa_persediaan_id: " . ($bb->coa_persediaan_id ?? 'NULL') . "\n";
    echo "  COA Query Result: " . ($coa ? "Found (ID {$coa->id})" : "NOT FOUND") . "\n";
    echo "  data-coa-kode=\"{$coaKode}\"\n";
    echo "  data-coa-nama=\"{$coaNama}\"\n";
    echo "  HTML: <option value=\"{$bb->id}\" data-coa-kode=\"{$coaKode}\" data-coa-nama=\"{$coaNama}\">{$bb->nama_bahan}</option>\n";
    echo "\n";
}

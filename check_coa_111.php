<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING COA WITH KODE_AKUN = '111' ===\n\n";

$coa = DB::table('coas')
    ->where('kode_akun', '111')
    ->first();

if ($coa) {
    echo "COA Found!\n";
    echo "ID: {$coa->id}\n";
    echo "Kode Akun: {$coa->kode_akun}\n";
    echo "Nama Akun: {$coa->nama_akun}\n";
    echo "Tipe Akun: {$coa->tipe_akun}\n";
    echo "Saldo Normal: {$coa->saldo_normal}\n";
    echo "Saldo Awal: " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n";
} else {
    echo "COA with kode_akun = '111' NOT FOUND!\n\n";
    
    echo "=== AVAILABLE KAS/BANK COAs ===\n";
    $kasBank = DB::table('coas')
        ->where('tipe_akun', 'Asset')
        ->where(function($query) {
            $query->where('nama_akun', 'like', '%kas%')
                  ->orWhere('nama_akun', 'like', '%bank%');
        })
        ->orderBy('kode_akun')
        ->get();
    
    foreach ($kasBank as $kb) {
        echo "ID: {$kb->id} | Kode: {$kb->kode_akun} | Nama: {$kb->nama_akun}\n";
    }
}

echo "\n=== CHECK COMPLETE ===\n";

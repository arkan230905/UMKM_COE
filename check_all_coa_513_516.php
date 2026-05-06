<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECK ALL COA 513-516 ===\n\n";

$coasToCheck = ['513', '514', '515', '516'];

foreach ($coasToCheck as $kode) {
    echo "Kode Akun: {$kode}\n";
    echo str_repeat("-", 60) . "\n";
    
    $coas = DB::table('coas')
        ->where('kode_akun', $kode)
        ->get(['id', 'user_id', 'kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun']);
    
    if ($coas->isEmpty()) {
        echo "  No COA found\n";
    } else {
        foreach ($coas as $coa) {
            $user = DB::table('users')->where('id', $coa->user_id)->first();
            echo "  ID: {$coa->id}\n";
            echo "  User: {$coa->user_id}";
            if ($user) {
                echo " ({$user->name})";
            } else {
                echo " (USER NOT FOUND - ORPHANED DATA)";
            }
            echo "\n";
            echo "  Nama: {$coa->nama_akun}\n";
            echo "  Tipe: {$coa->tipe_akun}\n";
            echo "  Kategori: {$coa->kategori_akun}\n";
            
            // Check if correct
            if ($coa->tipe_akun === 'Biaya' && $coa->kategori_akun === 'Biaya') {
                echo "  Status: ✅ CORRECT\n";
            } else {
                echo "  Status: ❌ WRONG (should be Biaya/Biaya)\n";
            }
            echo "\n";
        }
    }
    
    echo "\n";
}

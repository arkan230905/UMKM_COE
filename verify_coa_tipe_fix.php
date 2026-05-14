<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFY COA TIPE FIX ===\n\n";

$coasToCheck = ['513', '514', '515', '516'];

// Check for wrong tipe
$wrongCoas = DB::table('coas')
    ->whereIn('kode_akun', $coasToCheck)
    ->where('tipe_akun', '!=', 'Biaya')
    ->get(['user_id', 'kode_akun', 'nama_akun', 'tipe_akun']);

if ($wrongCoas->isEmpty()) {
    echo "✅ ALL COA TIPE CORRECT!\n\n";
    echo "No COA with kode 513-516 using 'Equity' tipe\n";
    echo "All are now using 'Biaya' tipe\n\n";
    
    // Show correct data
    echo "Verification:\n";
    $correctCoas = DB::table('coas')
        ->whereIn('kode_akun', $coasToCheck)
        ->where('tipe_akun', 'Biaya')
        ->get(['user_id', 'kode_akun', 'nama_akun', 'tipe_akun']);
    
    foreach ($correctCoas as $coa) {
        echo "  ✅ User {$coa->user_id}: {$coa->kode_akun} - {$coa->nama_akun} (Tipe: {$coa->tipe_akun})\n";
    }
    
    exit(0);
} else {
    echo "❌ STILL HAVE WRONG TIPE:\n\n";
    
    foreach ($wrongCoas as $coa) {
        echo "  ❌ User {$coa->user_id}: {$coa->kode_akun} - {$coa->nama_akun} (Tipe: {$coa->tipe_akun})\n";
    }
    
    exit(1);
}

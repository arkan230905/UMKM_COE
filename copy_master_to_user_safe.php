<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Copying master COA to users (SAFE MODE)...\n\n";

$users = DB::table('users')->get();
echo "Found " . count($users) . " users\n\n";

foreach ($users as $user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    
    // Cek COA user
    $userCoaCount = DB::table('coas')->where('user_id', $user->id)->count();
    
    if ($userCoaCount > 0) {
        echo "  ✅ Already has {$userCoaCount} COA (skipped)\n\n";
        continue;
    }
    
    // Copy master COA
    $masterCoas = DB::table('coas')->whereNull('user_id')->get();
    
    if ($masterCoas->isEmpty()) {
        echo "  ⚠️  No master COA found\n\n";
        continue;
    }
    
    echo "  📋 Copying " . count($masterCoas) . " COA...\n";
    
    foreach ($masterCoas as $coa) {
        DB::table('coas')->insert([
            'user_id' => $user->id,
            'kode_akun' => $coa->kode_akun,
            'nama_akun' => $coa->nama_akun,
            'tipe_akun' => $coa->tipe_akun,
            'kategori_akun' => $coa->kategori_akun ?? '',
            'is_akun_header' => $coa->is_akun_header ?? false,
            'saldo_normal' => $coa->saldo_normal,
            'saldo_awal' => $coa->saldo_awal ?? 0,
            'posted_saldo_awal' => $coa->posted_saldo_awal ?? false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    echo "  ✅ Done!\n\n";
}

echo "🎉 Finished!\n";
echo "\nNOTE: Master data (user_id = NULL) NOT deleted for safety.\n";
echo "You can delete it manually later if needed.\n";

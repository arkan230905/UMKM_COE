<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Copying master Satuan to users (SAFE MODE)...\n\n";

$users = DB::table('users')->get();
echo "Found " . count($users) . " users\n\n";

foreach ($users as $user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    
    // Cek Satuan user
    $userSatuanCount = DB::table('satuans')->where('user_id', $user->id)->count();
    
    if ($userSatuanCount > 0) {
        echo "  ✅ Already has {$userSatuanCount} Satuan (skipped)\n\n";
        continue;
    }
    
    // Copy master Satuan
    $masterSatuans = DB::table('satuans')->whereNull('user_id')->get();
    
    if ($masterSatuans->isEmpty()) {
        echo "  ⚠️  No master Satuan found\n";
        echo "  📋 Creating new Satuan from seeder...\n";
        
        // Run seeder
        $seeder = new \Database\Seeders\DefaultSatuanSeeder();
        $seeder->run($user->id);
        
        echo "  ✅ Created 16 Satuan\n\n";
        continue;
    }
    
    echo "  📋 Copying " . count($masterSatuans) . " Satuan...\n";
    
    foreach ($masterSatuans as $satuan) {
        DB::table('satuans')->insert([
            'user_id' => $user->id,
            'kode' => $satuan->kode,
            'nama' => $satuan->nama,
            'tipe' => $satuan->tipe ?? 'unit',
            'kategori' => $satuan->kategori ?? 'jumlah',
            'is_dasar' => $satuan->is_dasar ?? false,
            'is_active' => $satuan->is_active ?? true,
            'nilai_konversi' => $satuan->nilai_konversi ?? 1.0,
            'faktor_ke_dasar' => $satuan->faktor_ke_dasar ?? 1.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    echo "  ✅ Done!\n\n";
}

echo "🎉 Finished!\n";
echo "\nNOTE: Master data (user_id = NULL) NOT deleted for safety.\n";

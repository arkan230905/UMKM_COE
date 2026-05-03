<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Updating COA and Satuan data for existing users...\n\n";

// Get all users
$users = DB::table('users')->get();

echo "Found " . count($users) . " users\n\n";

foreach ($users as $user) {
    echo "Processing user: {$user->name} (ID: {$user->id})\n";
    
    // ========================================
    // UPDATE COA
    // ========================================
    
    // Check if user already has COA
    $existingCoa = DB::table('coas')->where('user_id', $user->id)->count();
    
    if ($existingCoa > 0) {
        echo "  ✅ User already has {$existingCoa} COA\n";
    } else {
        // Get master COA (user_id = NULL)
        $masterCoas = DB::table('coas')->whereNull('user_id')->get();
        
        if ($masterCoas->isEmpty()) {
            echo "  ⚠️  No master COA found\n";
        } else {
            echo "  📋 Copying " . count($masterCoas) . " COA from master...\n";
            
            // Copy master COA to user
            foreach ($masterCoas as $masterCoa) {
                DB::table('coas')->insert([
                    'user_id' => $user->id,
                    'kode_akun' => $masterCoa->kode_akun,
                    'nama_akun' => $masterCoa->nama_akun,
                    'tipe_akun' => $masterCoa->tipe_akun,
                    'kategori_akun' => $masterCoa->kategori_akun ?? '',
                    'is_akun_header' => $masterCoa->is_akun_header ?? false,
                    'saldo_normal' => $masterCoa->saldo_normal,
                    'saldo_awal' => $masterCoa->saldo_awal ?? 0,
                    'posted_saldo_awal' => $masterCoa->posted_saldo_awal ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            echo "  ✅ Copied " . count($masterCoas) . " COA to user {$user->id}\n";
        }
    }
    
    // ========================================
    // UPDATE SATUAN
    // ========================================
    
    // Check if user already has Satuan
    $existingSatuan = DB::table('satuans')->where('user_id', $user->id)->count();
    
    if ($existingSatuan > 0) {
        echo "  ✅ User already has {$existingSatuan} Satuan\n";
    } else {
        // Get master Satuan (user_id = NULL)
        $masterSatuans = DB::table('satuans')->whereNull('user_id')->get();
        
        if ($masterSatuans->isEmpty()) {
            echo "  ⚠️  No master Satuan found\n";
        } else {
            echo "  📋 Copying " . count($masterSatuans) . " Satuan from master...\n";
            
            // Copy master Satuan to user
            foreach ($masterSatuans as $masterSatuan) {
                DB::table('satuans')->insert([
                    'user_id' => $user->id,
                    'kode' => $masterSatuan->kode,
                    'nama' => $masterSatuan->nama,
                    'tipe' => $masterSatuan->tipe ?? 'unit',
                    'kategori' => $masterSatuan->kategori ?? 'jumlah',
                    'is_dasar' => $masterSatuan->is_dasar ?? false,
                    'is_active' => $masterSatuan->is_active ?? true,
                    'nilai_konversi' => $masterSatuan->nilai_konversi ?? 1.0,
                    'faktor_ke_dasar' => $masterSatuan->faktor_ke_dasar ?? 1.0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            echo "  ✅ Copied " . count($masterSatuans) . " Satuan to user {$user->id}\n";
        }
    }
    
    echo "\n";
}

// ========================================
// DELETE MASTER DATA
// ========================================

echo "🗑️  Deleting master COA and Satuan (user_id = NULL)...\n";

$deletedCoa = DB::table('coas')->whereNull('user_id')->delete();
echo "✅ Deleted {$deletedCoa} master COA\n";

$deletedSatuan = DB::table('satuans')->whereNull('user_id')->delete();
echo "✅ Deleted {$deletedSatuan} master Satuan\n";

echo "\n🎉 Done!\n";

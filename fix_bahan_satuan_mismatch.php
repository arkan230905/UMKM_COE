<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔧 Fixing Bahan Baku & Pendukung Satuan Mismatch\n";
echo "=================================================\n\n";

// Get all users
$users = DB::table('users')->get(['id', 'name']);

foreach ($users as $user) {
    echo "Processing User {$user->id}: {$user->name}\n";
    
    // Fix Bahan Baku
    $bahanBakus = DB::table('bahan_bakus')
        ->where('user_id', $user->id)
        ->get();
    
    foreach ($bahanBakus as $bahan) {
        // Get the satuan that this bahan is using
        $oldSatuan = DB::table('satuans')->where('id', $bahan->satuan_id)->first();
        
        if (!$oldSatuan) {
            echo "  ⚠️ Bahan Baku ID {$bahan->id} has invalid satuan_id {$bahan->satuan_id}\n";
            continue;
        }
        
        // If satuan user_id doesn't match, find the correct one for this user
        if ($oldSatuan->user_id != $user->id) {
            $correctSatuan = DB::table('satuans')
                ->where('user_id', $user->id)
                ->where('kode', $oldSatuan->kode)
                ->first();
            
            if ($correctSatuan) {
                DB::table('bahan_bakus')
                    ->where('id', $bahan->id)
                    ->update(['satuan_id' => $correctSatuan->id]);
                
                echo "  ✅ Fixed Bahan Baku '{$bahan->nama_bahan}': {$oldSatuan->kode} (ID {$bahan->satuan_id} -> {$correctSatuan->id})\n";
            } else {
                echo "  ❌ Cannot find matching satuan '{$oldSatuan->kode}' for user {$user->id}\n";
            }
        }
    }
    
    // Fix Bahan Pendukung
    $bahanPendukungs = DB::table('bahan_pendukungs')
        ->where('user_id', $user->id)
        ->get();
    
    foreach ($bahanPendukungs as $bahan) {
        // Get the satuan that this bahan is using
        $oldSatuan = DB::table('satuans')->where('id', $bahan->satuan_id)->first();
        
        if (!$oldSatuan) {
            echo "  ⚠️ Bahan Pendukung ID {$bahan->id} has invalid satuan_id {$bahan->satuan_id}\n";
            continue;
        }
        
        // If satuan user_id doesn't match, find the correct one for this user
        if ($oldSatuan->user_id != $user->id) {
            $correctSatuan = DB::table('satuans')
                ->where('user_id', $user->id)
                ->where('kode', $oldSatuan->kode)
                ->first();
            
            if ($correctSatuan) {
                DB::table('bahan_pendukungs')
                    ->where('id', $bahan->id)
                    ->update(['satuan_id' => $correctSatuan->id]);
                
                echo "  ✅ Fixed Bahan Pendukung '{$bahan->nama_bahan}': {$oldSatuan->kode} (ID {$bahan->satuan_id} -> {$correctSatuan->id})\n";
            } else {
                echo "  ❌ Cannot find matching satuan '{$oldSatuan->kode}' for user {$user->id}\n";
            }
        }
    }
    
    echo "\n";
}

echo "🎉 Done!\n";
echo "\nPlease refresh the Bahan Baku and Bahan Pendukung pages to see the fix.\n";

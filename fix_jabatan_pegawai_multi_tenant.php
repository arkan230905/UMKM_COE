<?php
/**
 * FIX JABATAN PEGAWAI MULTI-TENANT
 * 
 * Script untuk memperbaiki inkonsistensi data jabatan dan pegawai
 * dengan memastikan multi-tenant isolation
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIX JABATAN PEGAWAI MULTI-TENANT ===\n\n";

// Get all users
$users = DB::table('users')->get(['id', 'name', 'email']);

if ($users->isEmpty()) {
    echo "❌ Tidak ada user di database!\n";
    exit(1);
}

echo "Found " . $users->count() . " users\n\n";

foreach ($users as $user) {
    echo "Processing User ID: {$user->id} ({$user->name})\n";
    echo str_repeat('-', 60) . "\n";
    
    $fixed = 0;
    $errors = 0;
    
    // 1. Fix pegawai tanpa jabatan_id
    echo "1. Checking pegawai tanpa jabatan_id...\n";
    
    $pegawaisNoJabatanId = DB::table('pegawais')
        ->where('user_id', $user->id)
        ->whereNull('jabatan_id')
        ->whereNotNull('jabatan')
        ->get(['id', 'nama', 'jabatan', 'kategori']);
    
    if ($pegawaisNoJabatanId->isNotEmpty()) {
        echo "   Found {$pegawaisNoJabatanId->count()} pegawai without jabatan_id\n";
        
        foreach ($pegawaisNoJabatanId as $pegawai) {
            // Try to find matching jabatan by name
            $jabatan = DB::table('jabatans')
                ->where('user_id', $user->id)
                ->whereRaw('LOWER(nama) = ?', [strtolower($pegawai->jabatan)])
                ->first();
            
            if ($jabatan) {
                // Update pegawai with jabatan_id
                DB::table('pegawais')
                    ->where('id', $pegawai->id)
                    ->update([
                        'jabatan_id' => $jabatan->id,
                        'jabatan' => $jabatan->nama, // Sync nama juga
                        'updated_at' => now()
                    ]);
                
                echo "      ✅ Fixed: {$pegawai->nama} -> jabatan_id = {$jabatan->id} ({$jabatan->nama})\n";
                $fixed++;
            } else {
                echo "      ⚠️  Warning: {$pegawai->nama} -> jabatan '{$pegawai->jabatan}' not found in jabatans table\n";
                $errors++;
            }
        }
    } else {
        echo "   ✅ All pegawai have jabatan_id\n";
    }
    
    echo "\n";
    
    // 2. Fix pegawai dengan jabatan_id yang tidak valid (tidak ada di jabatans)
    echo "2. Checking pegawai dengan invalid jabatan_id...\n";
    
    $pegawaisInvalidJabatan = DB::table('pegawais as p')
        ->leftJoin('jabatans as j', function($join) use ($user) {
            $join->on('p.jabatan_id', '=', 'j.id')
                 ->where('j.user_id', '=', $user->id);
        })
        ->where('p.user_id', $user->id)
        ->whereNotNull('p.jabatan_id')
        ->whereNull('j.id')
        ->get(['p.id', 'p.nama', 'p.jabatan', 'p.jabatan_id']);
    
    if ($pegawaisInvalidJabatan->isNotEmpty()) {
        echo "   Found {$pegawaisInvalidJabatan->count()} pegawai with invalid jabatan_id\n";
        
        foreach ($pegawaisInvalidJabatan as $pegawai) {
            // Try to find matching jabatan by name
            $jabatan = DB::table('jabatans')
                ->where('user_id', $user->id)
                ->whereRaw('LOWER(nama) = ?', [strtolower($pegawai->jabatan)])
                ->first();
            
            if ($jabatan) {
                // Update pegawai with correct jabatan_id
                DB::table('pegawais')
                    ->where('id', $pegawai->id)
                    ->update([
                        'jabatan_id' => $jabatan->id,
                        'jabatan' => $jabatan->nama,
                        'updated_at' => now()
                    ]);
                
                echo "      ✅ Fixed: {$pegawai->nama} -> jabatan_id = {$jabatan->id} (was: {$pegawai->jabatan_id})\n";
                $fixed++;
            } else {
                // Set jabatan_id to NULL if not found
                DB::table('pegawais')
                    ->where('id', $pegawai->id)
                    ->update([
                        'jabatan_id' => null,
                        'updated_at' => now()
                    ]);
                
                echo "      ⚠️  Warning: {$pegawai->nama} -> jabatan '{$pegawai->jabatan}' not found, set jabatan_id to NULL\n";
                $errors++;
            }
        }
    } else {
        echo "   ✅ All jabatan_id are valid\n";
    }
    
    echo "\n";
    
    // 3. Sync nama jabatan (pegawais.jabatan != jabatans.nama)
    echo "3. Syncing jabatan names...\n";
    
    $pegawaisMismatchedName = DB::table('pegawais as p')
        ->join('jabatans as j', function($join) use ($user) {
            $join->on('p.jabatan_id', '=', 'j.id')
                 ->where('j.user_id', '=', $user->id);
        })
        ->where('p.user_id', $user->id)
        ->whereRaw('LOWER(p.jabatan) != LOWER(j.nama)')
        ->get(['p.id', 'p.nama as pegawai_nama', 'p.jabatan as old_jabatan', 'j.nama as new_jabatan']);
    
    if ($pegawaisMismatchedName->isNotEmpty()) {
        echo "   Found {$pegawaisMismatchedName->count()} pegawai with mismatched jabatan name\n";
        
        foreach ($pegawaisMismatchedName as $pegawai) {
            DB::table('pegawais')
                ->where('id', $pegawai->id)
                ->update([
                    'jabatan' => $pegawai->new_jabatan,
                    'updated_at' => now()
                ]);
            
            echo "      ✅ Synced: {$pegawai->pegawai_nama} -> '{$pegawai->old_jabatan}' => '{$pegawai->new_jabatan}'\n";
            $fixed++;
        }
    } else {
        echo "   ✅ All jabatan names are synced\n";
    }
    
    echo "\n";
    
    // 4. Verify multi-tenant isolation
    echo "4. Verifying multi-tenant isolation...\n";
    
    // Check if any pegawai has jabatan_id from different user
    $crossTenantPegawai = DB::table('pegawais as p')
        ->join('jabatans as j', 'p.jabatan_id', '=', 'j.id')
        ->where('p.user_id', $user->id)
        ->where('j.user_id', '!=', $user->id)
        ->count();
    
    if ($crossTenantPegawai > 0) {
        echo "   ❌ ERROR: Found {$crossTenantPegawai} pegawai with jabatan from different user!\n";
        echo "      This is a CRITICAL multi-tenant violation!\n";
        
        // Fix by setting jabatan_id to NULL
        $affected = DB::table('pegawais as p')
            ->join('jabatans as j', 'p.jabatan_id', '=', 'j.id')
            ->where('p.user_id', $user->id)
            ->where('j.user_id', '!=', $user->id)
            ->update([
                'p.jabatan_id' => null,
                'p.updated_at' => now()
            ]);
        
        echo "      ✅ Fixed: Set jabatan_id to NULL for {$affected} pegawai\n";
        $fixed += $affected;
    } else {
        echo "   ✅ Multi-tenant isolation is correct\n";
    }
    
    echo "\n";
    
    // Summary for this user
    echo "Summary for User {$user->id}:\n";
    echo "  Fixed: {$fixed}\n";
    echo "  Errors: {$errors}\n";
    echo "\n";
}

echo "=== FINAL VERIFICATION ===\n\n";

foreach ($users as $user) {
    echo "User {$user->id} ({$user->name}):\n";
    
    $totalPegawai = DB::table('pegawais')->where('user_id', $user->id)->count();
    $pegawaiWithJabatanId = DB::table('pegawais')->where('user_id', $user->id)->whereNotNull('jabatan_id')->count();
    $pegawaiWithoutJabatanId = $totalPegawai - $pegawaiWithJabatanId;
    
    echo "  Total Pegawai: {$totalPegawai}\n";
    echo "  With jabatan_id: {$pegawaiWithJabatanId}\n";
    echo "  Without jabatan_id: {$pegawaiWithoutJabatanId}\n";
    
    // Check BTKL specifically
    $pegawaiBtkl = DB::table('pegawais')
        ->where('user_id', $user->id)
        ->where('kategori', 'btkl')
        ->count();
    
    $pegawaiBtklWithJabatanId = DB::table('pegawais')
        ->where('user_id', $user->id)
        ->where('kategori', 'btkl')
        ->whereNotNull('jabatan_id')
        ->count();
    
    echo "  BTKL Pegawai: {$pegawaiBtkl}\n";
    echo "  BTKL with jabatan_id: {$pegawaiBtklWithJabatanId}\n";
    
    if ($pegawaiBtkl > 0 && $pegawaiBtklWithJabatanId == 0) {
        echo "  ⚠️  WARNING: BTKL pegawai tidak memiliki jabatan_id!\n";
    }
    
    echo "\n";
}

echo "=== COMPLETED ===\n";
echo "✅ All data has been checked and fixed.\n";
echo "   Please verify in your web application.\n";

<?php

/**
 * FIX COA TIPE - EQUITY TO BIAYA
 * 
 * Fix COA dengan kode 513-516 yang masih menggunakan tipe "Equity"
 * Seharusnya tipe "Biaya"
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIX COA TIPE - EQUITY TO BIAYA ===\n\n";

// COA yang perlu diperbaiki
$coasToFix = ['513', '514', '515', '516'];

echo "COA yang akan diperbaiki:\n";
echo "  513 - Beban Tunjangan\n";
echo "  514 - Beban Asuransi\n";
echo "  515 - Beban Bonus\n";
echo "  516 - Potongan Gaji\n\n";

echo "Dari tipe: Equity\n";
echo "Ke tipe: Biaya\n\n";

try {
    // Get all users
    $users = DB::table('users')->pluck('id');
    
    if ($users->isEmpty()) {
        echo "❌ No users found\n";
        exit(1);
    }
    
    echo "Found " . $users->count() . " users\n\n";
    
    $totalFixed = 0;
    
    foreach ($users as $userId) {
        $user = DB::table('users')->where('id', $userId)->first();
        echo "User {$userId} ({$user->name}):\n";
        
        foreach ($coasToFix as $kodeAkun) {
            $coa = DB::table('coas')
                ->where('user_id', $userId)
                ->where('kode_akun', $kodeAkun)
                ->first();
            
            if (!$coa) {
                echo "  ⚠️  COA {$kodeAkun} not found\n";
                continue;
            }
            
            if ($coa->tipe_akun === 'Equity') {
                // Update tipe_akun and kategori_akun
                DB::table('coas')
                    ->where('id', $coa->id)
                    ->update([
                        'tipe_akun' => 'Biaya',
                        'kategori_akun' => 'Biaya',
                        'updated_at' => now(),
                    ]);
                
                echo "  ✅ Fixed COA {$kodeAkun} ({$coa->nama_akun}): Equity → Biaya\n";
                $totalFixed++;
            } else {
                echo "  ℹ️  COA {$kodeAkun} already correct (tipe: {$coa->tipe_akun})\n";
            }
        }
        
        echo "\n";
    }
    
    echo "=== SUMMARY ===\n";
    echo "Total COA fixed: {$totalFixed}\n";
    echo "Users processed: " . $users->count() . "\n\n";
    
    if ($totalFixed > 0) {
        echo "✅ SUCCESS! COA tipe updated from Equity to Biaya\n";
    } else {
        echo "ℹ️  No COA needed fixing (all already correct)\n";
    }
    
    // Verify
    echo "\n=== VERIFICATION ===\n";
    foreach ($users as $userId) {
        $wrongCoas = DB::table('coas')
            ->where('user_id', $userId)
            ->whereIn('kode_akun', $coasToFix)
            ->where('tipe_akun', 'Equity')
            ->count();
        
        if ($wrongCoas > 0) {
            echo "❌ User {$userId} still has {$wrongCoas} COA with wrong tipe\n";
        } else {
            echo "✅ User {$userId} - All COA tipe correct\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n✅ Fix complete!\n";

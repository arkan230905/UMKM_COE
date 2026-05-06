<?php
/**
 * FIX USER 1 COA
 * 
 * User 1 only has 3 COAs, need to add the full 51 COAs
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIX USER 1 COA ===\n\n";

$userId = 1;

// Check current state
$user = DB::table('users')->where('id', $userId)->first();
echo "User: {$user->name} ({$user->email})\n";

$existingCoa = DB::table('coas')->where('user_id', $userId)->count();
echo "Current COA count: {$existingCoa}\n\n";

if ($existingCoa >= 51) {
    echo "✅ User already has sufficient COA ({$existingCoa})\n";
    exit(0);
}

echo "Deleting existing {$existingCoa} COA records...\n";
DB::table('coas')->where('user_id', $userId)->delete();
echo "✅ Deleted\n\n";

echo "Running DefaultCoaSeeder...\n";

try {
    $seeder = new \Database\Seeders\DefaultCoaSeeder();
    $seeder->run($userId);
    
    $newCoaCount = DB::table('coas')->where('user_id', $userId)->count();
    
    echo "✅ SUCCESS! Created {$newCoaCount} COA records\n\n";
    
    // Verify important COAs
    echo "Verifying important COAs:\n";
    $importantCoas = ['1141', '1161', '1171', '1172', '1173', '211', '550'];
    
    foreach ($importantCoas as $kode) {
        $coa = DB::table('coas')
            ->where('user_id', $userId)
            ->where('kode_akun', $kode)
            ->first();
        
        if ($coa) {
            echo "  ✅ {$kode}: {$coa->nama_akun}\n";
        } else {
            echo "  ❌ {$kode}: NOT FOUND\n";
        }
    }
    
    echo "\n✅ User 1 COA fixed!\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

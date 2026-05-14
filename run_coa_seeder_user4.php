<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Running DefaultCoaSeeder for User ID 4 (ownernayla)...\n\n";

// Check current COA count
$currentCount = DB::table('coas')->where('user_id', 4)->count();
echo "Current COA count for user 4: $currentCount\n";

// Run seeder
$seeder = new \Database\Seeders\DefaultCoaSeeder();
$seeder->run(4);

// Check new count
$newCount = DB::table('coas')->where('user_id', 4)->count();
echo "New COA count for user 4: $newCount\n";

if ($newCount > $currentCount) {
    echo "\n✅ SUCCESS! Added " . ($newCount - $currentCount) . " new COA accounts\n";
} else {
    echo "\n⚠️ No new COA added (user already has COA or seeder skipped)\n";
}

// Show all COA for user 4
echo "\n📋 All COA for user 4:\n";
$coas = DB::table('coas')->where('user_id', 4)->orderBy('kode_akun')->get(['kode_akun', 'nama_akun', 'tipe_akun']);
foreach ($coas as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})\n";
}

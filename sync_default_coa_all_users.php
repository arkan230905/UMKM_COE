<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SYNC DEFAULT COA TO ALL USERS ===\n\n";

// Get template COA from User 1
$templateCoas = DB::table('coas')
    ->where('user_id', 1)
    ->get();

echo "Template COA count: {$templateCoas->count()}\n\n";

// Get all users except User 1
$users = DB::table('users')
    ->where('id', '!=', 1)
    ->get(['id', 'name', 'email']);

echo "Found " . $users->count() . " users to sync\n\n";

foreach ($users as $user) {
    echo "--- User {$user->id}: {$user->name} ({$user->email}) ---\n";
    
    // Check existing COA
    $existingCoas = DB::table('coas')
        ->where('user_id', $user->id)
        ->pluck('kode_akun')
        ->toArray();
    
    echo "  Existing COA: " . count($existingCoas) . "\n";
    
    $inserted = 0;
    $skipped = 0;
    
    foreach ($templateCoas as $coa) {
        // Skip if already exists
        if (in_array($coa->kode_akun, $existingCoas)) {
            $skipped++;
            continue;
        }
        
        // Insert new COA
        DB::table('coas')->insert([
            'user_id' => $user->id,
            'company_id' => $coa->company_id,
            'kode_akun' => $coa->kode_akun,
            'nama_akun' => $coa->nama_akun,
            'tipe_akun' => $coa->tipe_akun,
            'kategori_akun' => $coa->kategori_akun,
            'saldo_normal' => $coa->saldo_normal,
            'saldo_awal' => 0,
            'is_akun_header' => $coa->is_akun_header ?? false,
            'kode_induk' => $coa->kode_induk ?? null,
            'keterangan' => $coa->keterangan ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $inserted++;
    }
    
    $finalCount = DB::table('coas')->where('user_id', $user->id)->count();
    
    echo "  ✓ Inserted: {$inserted}\n";
    echo "  ✓ Skipped: {$skipped}\n";
    echo "  ✓ Total COA: {$finalCount}\n\n";
}

echo "✅ SYNC COMPLETED!\n";

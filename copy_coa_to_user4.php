<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COPY COA FROM USER 1 TO USER 4 ===\n\n";

$sourceUserId = 1; // User 1 (Admin)
$targetUserId = 4; // User 4 (ownernayla)

// Get all COA from User 1
$sourceCoas = DB::table('coas')
    ->where('user_id', $sourceUserId)
    ->get();

echo "Found {$sourceCoas->count()} COA from User {$sourceUserId}\n";

// Check existing COA for User 4
$existingCoas = DB::table('coas')
    ->where('user_id', $targetUserId)
    ->pluck('kode_akun')
    ->toArray();

echo "User {$targetUserId} already has " . count($existingCoas) . " COA\n\n";

$inserted = 0;
$skipped = 0;

foreach ($sourceCoas as $coa) {
    // Skip if already exists
    if (in_array($coa->kode_akun, $existingCoas)) {
        $skipped++;
        continue;
    }
    
    // Insert new COA for User 4
    DB::table('coas')->insert([
        'user_id' => $targetUserId,
        'company_id' => $coa->company_id,
        'kode_akun' => $coa->kode_akun,
        'nama_akun' => $coa->nama_akun,
        'tipe_akun' => $coa->tipe_akun,
        'kategori_akun' => $coa->kategori_akun,
        'saldo_normal' => $coa->saldo_normal,
        'saldo_awal' => 0, // Reset saldo awal
        'is_akun_header' => $coa->is_akun_header ?? false,
        'kode_induk' => $coa->kode_induk ?? null,
        'keterangan' => $coa->keterangan ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $inserted++;
}

echo "✓ Inserted: {$inserted} COA\n";
echo "✓ Skipped: {$skipped} COA (already exists)\n";

// Verify
$finalCount = DB::table('coas')->where('user_id', $targetUserId)->count();
echo "\n✅ User {$targetUserId} now has {$finalCount} COA\n";

// Show sample
echo "\nSample COA for User {$targetUserId}:\n";
$samples = DB::table('coas')
    ->where('user_id', $targetUserId)
    ->orderBy('kode_akun')
    ->limit(10)
    ->get(['kode_akun', 'nama_akun']);

foreach ($samples as $sample) {
    echo "  {$sample->kode_akun} - {$sample->nama_akun}\n";
}

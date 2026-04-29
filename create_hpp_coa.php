<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating HPP COA Accounts...\n\n";

// Get current user ID (assuming user 1 for this example)
$userId = 1;

// Check if HPP COA already exists
$existingHppCoa = \App\Models\Coa::withoutGlobalScopes()
    ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
    ->where('nama_akun', 'like', '%HPP%')
    ->where('user_id', $userId)
    ->first();

if ($existingHppCoa) {
    echo "HPP COA already exists: {$existingHppCoa->kode_akun} - {$existingHppCoa->nama_akun}\n";
} else {
    // Create HPP COA
    $hppCoa = new \App\Models\Coa();
    $hppCoa->kode_akun = '52'; // Use existing BTKL code or create new
    $hppCoa->nama_akun = 'HPP Penjualan';
    $hppCoa->tipe_akun = 'Beban';
    $hppCoa->kategori_akun = 'HPP';
    $hppCoa->saldo_normal = 'debit';
    $hppCoa->saldo_awal = 0;
    $hppCoa->user_id = $userId;
    
    // Check if kode 52 already exists
    $existingCoa52 = \App\Models\Coa::withoutGlobalScopes()
        ->where('kode_akun', '52')
        ->where('user_id', $userId)
        ->first();
    
    if ($existingCoa52) {
        // Use next available code
        $maxCode = \App\Models\Coa::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('kode_akun', 'like', '5%')
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) DESC')
            ->first();
        
        if ($maxCode) {
            $nextCode = (int)$maxCode->kode_akun + 1;
            $hppCoa->kode_akun = (string)$nextCode;
        } else {
            $hppCoa->kode_akun = '521'; // Start from 521
        }
    }
    
    $hppCoa->save();
    
    echo "Created HPP COA: {$hppCoa->kode_akun} - {$hppCoa->nama_akun}\n";
}

// Also create specific HPP for Jasuke if needed
$jasukeHppCoa = \App\Models\Coa::withoutGlobalScopes()
    ->where('nama_akun', 'HPP Jasuke')
    ->where('user_id', $userId)
    ->first();

if (!$jasukeHppCoa) {
    $jasukeHppCoa = new \App\Models\Coa();
    $jasukeHppCoa->kode_akun = '522'; // Next available code
    $jasukeHppCoa->nama_akun = 'HPP Jasuke';
    $jasukeHppCoa->tipe_akun = 'Beban';
    $jasukeHppCoa->kategori_akun = 'HPP';
    $jasukeHppCoa->saldo_normal = 'debit';
    $jasukeHppCoa->saldo_awal = 0;
    $jasukeHppCoa->user_id = $userId;
    $jasukeHppCoa->save();
    
    echo "Created HPP Jasuke COA: {$jasukeHppCoa->kode_akun} - {$jasukeHppCoa->nama_akun}\n";
}

echo "\nCOA HPP creation completed!\n";

// Verify the created COAs
echo "\n=== VERIFICATION ===\n";
$allHppCoas = \App\Models\Coa::withoutGlobalScopes()
    ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost'])
    ->where('nama_akun', 'like', '%HPP%')
    ->where('user_id', $userId)
    ->get();

echo "Total HPP COAs: {$allHppCoas->count()}\n";
foreach ($allHppCoas as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})\n";
}

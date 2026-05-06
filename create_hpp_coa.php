<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREATING HPP COA ===" . PHP_EOL . PHP_EOL;

// Get user
$user = \App\Models\User::first();
if (!$user) {
    echo "❌ No user found" . PHP_EOL;
    exit(1);
}

echo "✅ Using user: {$user->name} (ID: {$user->id})" . PHP_EOL . PHP_EOL;

// Check if COA 560 already exists
$existing = \App\Models\Coa::where('kode_akun', '560')->first();
if ($existing) {
    echo "✅ COA 560 already exists: {$existing->nama_akun}" . PHP_EOL;
    exit(0);
}

// Create COA 560 (HPP)
try {
    $coa = \App\Models\Coa::create([
        'user_id' => $user->id,
        'kode_akun' => '560',
        'nama_akun' => 'Harga Pokok Penjualan (HPP)',
        'tipe_akun' => 'Expense',
        'kategori_akun' => 'Beban Pokok Penjualan',
        'saldo_normal' => 'Debit',
        'parent_id' => null,
        'is_active' => true,
    ]);
    
    echo "✅ COA 560 created successfully!" . PHP_EOL;
    echo "   - Kode: {$coa->kode_akun}" . PHP_EOL;
    echo "   - Nama: {$coa->nama_akun}" . PHP_EOL;
    echo "   - Tipe: {$coa->tipe_akun}" . PHP_EOL;
    echo "   - Saldo Normal: {$coa->saldo_normal}" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error creating COA: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

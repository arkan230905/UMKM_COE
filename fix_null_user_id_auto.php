<?php
// Script untuk fix user_id yang NULL - AUTO ASSIGN ke User ID 4 (Muhammad Arkan Abiyyu)
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING NULL USER_ID (AUTO) ===\n\n";

// Target user ID (Muhammad Arkan Abiyyu dari screenshot)
$userId = 4;

$user = \App\Models\User::find($userId);
if (!$user) {
    echo "User ID {$userId} tidak ditemukan!\n";
    exit(1);
}

echo "Target User: {$user->name} ({$user->email})\n\n";

// Cari bahan baku dengan user_id NULL
$bahanBakuNull = \App\Models\BahanBaku::whereNull('user_id')->get();
echo "Bahan Baku dengan user_id NULL: " . $bahanBakuNull->count() . "\n";

// Update bahan baku
$updatedBB = 0;
foreach ($bahanBakuNull as $bb) {
    echo "  - Updating: ID {$bb->id} | {$bb->nama_bahan} | Saldo Awal: {$bb->saldo_awal}\n";
    $bb->user_id = $userId;
    $bb->save();
    $updatedBB++;
}

// Cari bahan pendukung dengan user_id NULL
$bahanPendukungNull = \App\Models\BahanPendukung::whereNull('user_id')->get();
echo "\nBahan Pendukung dengan user_id NULL: " . $bahanPendukungNull->count() . "\n";

// Update bahan pendukung
$updatedBP = 0;
foreach ($bahanPendukungNull as $bp) {
    echo "  - Updating: ID {$bp->id} | {$bp->nama_bahan} | Saldo Awal: {$bp->saldo_awal}\n";
    $bp->user_id = $userId;
    $bp->save();
    $updatedBP++;
}

echo "\n=== DONE ===\n";
echo "✓ Total Bahan Baku updated: {$updatedBB}\n";
echo "✓ Total Bahan Pendukung updated: {$updatedBP}\n";
echo "\n🎉 Data sekarang sudah muncul di halaman bahan baku dan bahan pendukung!\n";
echo "Silakan refresh browser Anda.\n";

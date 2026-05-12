<?php
// Script untuk fix user_id yang NULL
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING NULL USER_ID ===\n\n";

// Cari bahan baku dengan user_id NULL
$bahanBakuNull = \App\Models\BahanBaku::whereNull('user_id')->get();
echo "Bahan Baku dengan user_id NULL: " . $bahanBakuNull->count() . "\n";

foreach ($bahanBakuNull as $bb) {
    echo "  - ID: {$bb->id} | Nama: {$bb->nama_bahan}\n";
}

// Cari bahan pendukung dengan user_id NULL
$bahanPendukungNull = \App\Models\BahanPendukung::whereNull('user_id')->get();
echo "\nBahan Pendukung dengan user_id NULL: " . $bahanPendukungNull->count() . "\n";

foreach ($bahanPendukungNull as $bp) {
    echo "  - ID: {$bp->id} | Nama: {$bp->nama_bahan}\n";
}

echo "\n--- PILIH USER UNTUK ASSIGN ---\n";
$users = \App\Models\User::all();
foreach ($users as $user) {
    echo "{$user->id}. {$user->name} ({$user->email})\n";
}

echo "\nMasukkan User ID yang akan di-assign (contoh: 4): ";
$userId = trim(fgets(STDIN));

if (empty($userId) || !is_numeric($userId)) {
    echo "User ID tidak valid!\n";
    exit(1);
}

$user = \App\Models\User::find($userId);
if (!$user) {
    echo "User tidak ditemukan!\n";
    exit(1);
}

echo "\nAnda akan assign data ke: {$user->name} ({$user->email})\n";
echo "Lanjutkan? (y/n): ";
$confirm = trim(fgets(STDIN));

if (strtolower($confirm) !== 'y') {
    echo "Dibatalkan.\n";
    exit(0);
}

// Update bahan baku
$updatedBB = 0;
foreach ($bahanBakuNull as $bb) {
    $bb->user_id = $userId;
    $bb->save();
    $updatedBB++;
    echo "✓ Updated Bahan Baku: {$bb->nama_bahan}\n";
}

// Update bahan pendukung
$updatedBP = 0;
foreach ($bahanPendukungNull as $bp) {
    $bp->user_id = $userId;
    $bp->save();
    $updatedBP++;
    echo "✓ Updated Bahan Pendukung: {$bp->nama_bahan}\n";
}

echo "\n=== DONE ===\n";
echo "Total Bahan Baku updated: {$updatedBB}\n";
echo "Total Bahan Pendukung updated: {$updatedBP}\n";
echo "\nSilakan refresh halaman bahan baku dan bahan pendukung!\n";

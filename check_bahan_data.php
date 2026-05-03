<?php
// Script untuk cek data bahan baku dan bahan pendukung
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING BAHAN BAKU & BAHAN PENDUKUNG DATA ===\n\n";

// Get all users
$users = \App\Models\User::all();
echo "Total Users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "User ID: {$user->id} - {$user->name} ({$user->email})\n";
}

echo "\n--- BAHAN BAKU ---\n";
$bahanBakus = \App\Models\BahanBaku::all();
echo "Total Bahan Baku (ALL): " . $bahanBakus->count() . "\n\n";

if ($bahanBakus->count() > 0) {
    foreach ($bahanBakus as $bb) {
        echo "ID: {$bb->id} | User ID: {$bb->user_id} | Nama: {$bb->nama_bahan} | Saldo Awal: {$bb->saldo_awal}\n";
    }
} else {
    echo "Tidak ada data bahan baku\n";
}

echo "\n--- BAHAN PENDUKUNG ---\n";
$bahanPendukungs = \App\Models\BahanPendukung::all();
echo "Total Bahan Pendukung (ALL): " . $bahanPendukungs->count() . "\n\n";

if ($bahanPendukungs->count() > 0) {
    foreach ($bahanPendukungs as $bp) {
        echo "ID: {$bp->id} | User ID: {$bp->user_id} | Nama: {$bp->nama_bahan} | Saldo Awal: {$bp->saldo_awal}\n";
    }
} else {
    echo "Tidak ada data bahan pendukung\n";
}

echo "\n--- COA PERSEDIAAN ---\n";
$coaPersediaan = \App\Models\Coa::whereIn('kode_akun', ['1101', '114', '1141', '1142', '1143', '1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'])
    ->get();
    
foreach ($coaPersediaan as $coa) {
    echo "Kode: {$coa->kode_akun} | User ID: {$coa->user_id} | Nama: {$coa->nama_akun} | Saldo Awal: {$coa->saldo_awal}\n";
}

echo "\n=== DONE ===\n";

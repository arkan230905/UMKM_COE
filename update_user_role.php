<?php

/**
 * Script untuk mengupdate role user
 * Jalankan dengan: php update_user_role.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Update User Role ===\n\n";

// Tampilkan semua user
$users = User::all();

if ($users->isEmpty()) {
    echo "Tidak ada user di database.\n";
    exit;
}

echo "Daftar User:\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s %-30s %-30s %-20s\n", "ID", "Name", "Email", "Current Role");
echo str_repeat("-", 80) . "\n";

foreach ($users as $user) {
    printf("%-5s %-30s %-30s %-20s\n", 
        $user->id, 
        substr($user->name, 0, 28), 
        substr($user->email, 0, 28), 
        $user->role ?? 'NULL'
    );
}

echo str_repeat("-", 80) . "\n\n";

// Tanya user mana yang mau diupdate
echo "Masukkan ID user yang ingin diupdate (atau 'exit' untuk keluar): ";
$userId = trim(fgets(STDIN));

if (strtolower($userId) === 'exit') {
    echo "Dibatalkan.\n";
    exit;
}

$user = User::find($userId);

if (!$user) {
    echo "User dengan ID $userId tidak ditemukan.\n";
    exit;
}

echo "\nUser dipilih: {$user->name} ({$user->email})\n";
echo "Role saat ini: " . ($user->role ?? 'NULL') . "\n\n";

echo "Pilih role baru:\n";
echo "1. admin\n";
echo "2. owner\n";
echo "3. pelanggan\n";
echo "4. pegawai_pembelian\n";
echo "\nMasukkan pilihan (1-4): ";

$choice = trim(fgets(STDIN));

$roles = [
    '1' => 'admin',
    '2' => 'owner',
    '3' => 'pelanggan',
    '4' => 'pegawai_pembelian',
];

if (!isset($roles[$choice])) {
    echo "Pilihan tidak valid.\n";
    exit;
}

$newRole = $roles[$choice];

// Update role
$user->role = $newRole;
$user->save();

echo "\n✓ Role user berhasil diupdate menjadi: $newRole\n";
echo "\nSekarang Anda bisa login dengan user ini dan mengakses dashboard.\n";

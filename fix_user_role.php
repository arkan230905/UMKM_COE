<?php

/**
 * Script untuk mengupdate role user pertama menjadi owner
 * Jalankan dengan: php fix_user_role.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Fix User Role ===\n\n";

// Ambil user pertama
$user = User::first();

if (!$user) {
    echo "Tidak ada user di database.\n";
    exit(1);
}

echo "User ditemukan:\n";
echo "  ID: {$user->id}\n";
echo "  Name: {$user->name}\n";
echo "  Email: {$user->email}\n";
echo "  Role saat ini: " . ($user->role ?? 'NULL') . "\n\n";

// Update role menjadi owner
$user->role = 'owner';
$user->save();

echo "✓ Role berhasil diupdate menjadi: owner\n\n";
echo "Sekarang Anda bisa login dengan:\n";
echo "  Email: {$user->email}\n";
echo "  Dan mengakses dashboard sebagai Owner.\n";

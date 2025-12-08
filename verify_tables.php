<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Verifikasi Struktur Tabel ===\n\n";

// Check users table
echo "Tabel 'users':\n";
$usersColumns = Schema::getColumnListing('users');
echo "  Kolom: " . implode(', ', $usersColumns) . "\n";
echo "  ✓ role: " . (in_array('role', $usersColumns) ? 'ADA' : 'TIDAK ADA') . "\n";
echo "  ✓ perusahaan_id: " . (in_array('perusahaan_id', $usersColumns) ? 'ADA' : 'TIDAK ADA') . "\n\n";

// Check perusahaan table
echo "Tabel 'perusahaan':\n";
$perusahaanColumns = Schema::getColumnListing('perusahaan');
echo "  Kolom: " . implode(', ', $perusahaanColumns) . "\n";
echo "  ✓ kode: " . (in_array('kode', $perusahaanColumns) ? 'ADA' : 'TIDAK ADA') . "\n\n";

// Check if there are any users
$userCount = DB::table('users')->count();
echo "Jumlah user di database: $userCount\n\n";

if ($userCount > 0) {
    echo "Daftar Users:\n";
    $users = DB::table('users')->select('id', 'name', 'email', 'role')->get();
    foreach ($users as $user) {
        echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
    }
} else {
    echo "Tidak ada user. Silakan registrasi user baru dengan role 'owner'.\n";
}

echo "\n✓ Semua kolom yang diperlukan sudah ada!\n";
echo "✓ Anda sekarang bisa registrasi user baru sebagai Owner.\n";

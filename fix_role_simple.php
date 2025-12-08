<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Update role user pertama
$affected = DB::table('users')->where('id', 1)->update(['role' => 'owner']);

if ($affected > 0) {
    echo "✓ Role user ID 1 berhasil diupdate menjadi 'owner'\n";
} else {
    echo "⚠ User ID 1 tidak ditemukan atau sudah memiliki role 'owner'\n";
}

// Tampilkan semua users
$users = DB::table('users')->select('id', 'name', 'email', 'role')->get();

echo "\nDaftar Users:\n";
echo str_repeat("-", 80) . "\n";
foreach ($users as $user) {
    echo "  ID: {$user->id} | Name: {$user->name} | Email: {$user->email} | Role: {$user->role}\n";
}
echo str_repeat("-", 80) . "\n";
echo "\nSilakan login dan akses dashboard!\n";

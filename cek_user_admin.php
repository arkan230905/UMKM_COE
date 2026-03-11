<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK USER ADMIN ===" . PHP_EOL;

$admins = \App\Models\User::where('role', 'admin')
    ->orWhere('role', 'owner')
    ->get();

echo "Jumlah user admin/owner: " . $admins->count() . PHP_EOL;

foreach($admins as $admin) {
    echo "ID: {$admin->id} | Email: {$admin->email} | Role: {$admin->role} | Name: {$admin->name}" . PHP_EOL;
}

if ($admins->isEmpty()) {
    echo PHP_EOL . "❌ Tidak ada user admin/owner ditemukan!" . PHP_EOL;
    echo "Membuat user admin default..." . PHP_EOL;
    
    try {
        $user = new \App\Models\User();
        $user->name = 'Admin User';
        $user->email = 'admin@example.com';
        $user->password = \Hash::make('password');
        $user->role = 'admin';
        $user->email_verified_at = now();
        $user->save();
        
        echo "✅ User admin berhasil dibuat:" . PHP_EOL;
        echo "   Email: admin@example.com" . PHP_EOL;
        echo "   Password: password" . PHP_EOL;
        
    } catch (Exception $e) {
        echo "❌ Gagal membuat user admin: " . $e->getMessage() . PHP_EOL;
    }
}

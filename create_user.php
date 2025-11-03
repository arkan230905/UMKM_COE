<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;

try {
    $user = User::create([
        'name' => 'Chindi Lestari',
        'username' => 'chindi',
        'email' => 'chindi1@gmail.com',
        'phone' => '089678989899',
        'password' => Hash::make('password123'),
    ]);
    
    echo "✅ User berhasil dibuat!\n";
    echo "Name: {$user->name}\n";
    echo "Username: {$user->username}\n";
    echo "Email: {$user->email}\n";
    echo "Phone: {$user->phone}\n";
    echo "\nSilakan login dengan:\n";
    echo "Email/Username: chindi atau chindi1@gmail.com\n";
    echo "Password: password123\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::all();

echo "=== DAFTAR USER ===\n";
foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Role: " . ($user->role ?? 'N/A') . "\n";
    echo "Created: {$user->created_at}\n";
    echo "-------------------\n";
}

if ($users->isEmpty()) {
    echo "Tidak ada user di database.\n";
}

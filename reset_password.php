<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(1);
if ($user) {
    $user->password = Hash::make('password');
    $user->save();
    echo "Password untuk {$user->email} sudah di-reset ke: password\n";
} else {
    echo "User tidak ditemukan\n";
}

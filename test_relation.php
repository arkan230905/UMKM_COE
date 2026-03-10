<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing pegawai relation...\n";

$user = App\Models\User::where('email', 'ahmad@gmail.com')->first();

if ($user) {
    echo "User found: " . $user->name . " (role: " . $user->role . ")\n";
    echo "User pegawai_id: " . $user->pegawai_id . "\n";
    
    $pegawai = $user->pegawai;
    if ($pegawai) {
        echo "✅ Pegawai relation works!\n";
        echo "Pegawai nama: " . $pegawai->nama . "\n";
        echo "Pegawai nomor_induk_pegawai: " . $pegawai->nomor_induk_pegawai . "\n";
    } else {
        echo "❌ Pegawai relation failed\n";
    }
} else {
    echo "❌ User not found\n";
}

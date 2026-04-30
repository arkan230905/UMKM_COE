<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating Missing Perusahaan Data...\n\n";

// Get current user
$user = \Illuminate\Support\Facades\Auth::user();
if (!$user) {
    \Illuminate\Support\Facades\Auth::loginUsingId(1);
    $user = \Illuminate\Support\Facades\Auth::user();
}

echo "Current User:\n";
echo "ID: {$user->id}\n";
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Role: {$user->role}\n";
echo "Perusahaan ID: " . ($user->perusahaan_id ?? 'NULL') . "\n\n";

// Check if perusahaan already exists
$existingPerusahaan = \App\Models\Perusahaan::find($user->perusahaan_id);
if ($existingPerusahaan) {
    echo "Perusahaan already exists:\n";
    echo "ID: {$existingPerusahaan->id}\n";
    echo "Nama: {$existingPerusahaan->nama}\n";
    echo "Email: {$existingPerusahaan->email}\n";
    echo "Telepon: {$existingPerusahaan->telepon}\n";
    echo "Alamat: {$existingPerusahaan->alamat}\n";
    exit;
}

// Create perusahaan data
echo "Creating new perusahaan data...\n";

$perusahaan = new \App\Models\Perusahaan();
$perusahaan->kode = 'UMKM001'; // Generate company code
$perusahaan->nama = 'UMKM COE'; // Default company name
$perusahaan->email = $user->email ?? 'info@umkmcoe.com';
$perusahaan->telepon = '08123456789';
$perusahaan->alamat = 'Jl. Contoh No. 123, Jakarta, Indonesia';
$perusahaan->catalog_description = 'Kami adalah perusahaan UMKM yang berdedikasi untuk menyediakan produk berkualitas tinggi dengan harga terjangkau. Kami mengutamakan kepuasan pelanggan dan selalu berinovasi dalam mengembangkan produk kami.';
$perusahaan->maps_link = 'https://maps.google.com/?q=Jakarta';
$perusahaan->latitude = -6.2088;
$perusahaan->longitude = 106.8456;
$perusahaan->save();

echo "Perusahaan created successfully!\n";
echo "ID: {$perusahaan->id}\n";
echo "Kode: {$perusahaan->kode}\n";
echo "Nama: {$perusahaan->nama}\n\n";

// Update user perusahaan_id
$user->perusahaan_id = $perusahaan->id;
$user->save();

echo "User perusahaan_id updated to: {$user->perusahaan_id}\n\n";

// Verify the data
echo "=== VERIFICATION ===\n";
$updatedPerusahaan = \App\Models\Perusahaan::find($user->perusahaan_id);
if ($updatedPerusahaan) {
    echo "Perusahaan data verified:\n";
    echo "ID: {$updatedPerusahaan->id}\n";
    echo "Nama: {$updatedPerusahaan->nama}\n";
    echo "Email: {$updatedPerusahaan->email}\n";
    echo "Telepon: {$updatedPerusahaan->telepon}\n";
    echo "Alamat: {$updatedPerusahaan->alamat}\n";
    echo "Catalog Description: " . ($updatedPerusahaan->catalog_description ?? 'NULL') . "\n";
}

echo "\nPerusahaan data creation completed!\n";

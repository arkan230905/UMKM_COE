<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing DashboardController queries...\n";

try {
    // Test all the queries that DashboardController uses
    $totalPegawai = \App\Models\Pegawai::count();
    echo "Pegawai count: {$totalPegawai}\n";
    
    $totalPresensi = \App\Models\Presensi::count();
    echo "Presensi count: {$totalPresensi}\n";
    
    $totalProduk = \App\Models\Produk::count();
    echo "Produk count: {$totalProduk}\n";
    
    $totalVendor = \App\Models\Vendor::count();
    echo "Vendor count: {$totalVendor}\n";
    
    $totalBahanBaku = \App\Models\BahanBaku::count();
    echo "BahanBaku count: {$totalBahanBaku}\n";
    
    $totalSatuan = \App\Models\Satuan::count();
    echo "Satuan count: {$totalSatuan}\n";
    
    $totalPelanggan = \App\Models\Pelanggan::count();
    echo "Pelanggan count: {$totalPelanggan}\n";
    
    $totalBahanPendukung = \App\Models\BahanPendukung::count();
    echo "BahanPendukung count: {$totalBahanPendukung}\n";
    
    $totalAset = \App\Models\Aset::count();
    echo "Aset count: {$totalAset}\n";
    
    $totalJabatan = \App\Models\Jabatan::count();
    echo "Jabatan count: {$totalJabatan}\n";
    
    // Test the specific query that was failing
    echo "\nTesting specific Jabatan query that was failing...\n";
    $jabatanTest = \App\Models\Jabatan::where('user_id', 1)->count();
    echo "Jabatan with user_id 1: {$jabatanTest}\n";
    
    echo "\nAll DashboardController queries completed successfully!\n";
    echo "No more 'user_id column not found' errors!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

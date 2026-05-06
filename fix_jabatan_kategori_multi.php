<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating individual kategori for each jabatan (multi-tenant)...\n\n";

// Get all users who have jabatans
$users = App\Models\User::whereHas('jabatans', function($query) {
    $query->whereNotNull('user_id');
})->get();

foreach ($users as $user) {
    echo "Processing user: " . $user->name . " (ID: " . $user->id . ")\n";
    
    // Get all jabatans for this user
    $jabatans = App\Models\Jabatan::where('user_id', $user->id)
        ->orderBy('id', 'asc')
        ->get();
    
    foreach ($jabatans as $index => $jabatan) {
        echo "  Processing jabatan: " . $jabatan->nama . " (Current ID: " . $jabatan->id . ")\n";
        
        // Create unique kategori for this jabatan
        $kategoriNama = $jabatan->nama . " - " . $user->name;
        
        // Check if kategori already exists
        $kategori = App\Models\KategoriPegawai::where('nama', $kategoriNama)->first();
        
        if (!$kategori) {
            $kategori = new App\Models\KategoriPegawai();
            $kategori->nama = $kategoriNama;
            $kategori->deskripsi = 'Kategori untuk jabatan ' . $jabatan->nama . ' milik ' . $user->name;
            $kategori->save();
            echo "    ✓ Created kategori: " . $kategoriNama . " (ID: " . $kategori->id . ")\n";
        } else {
            echo "    ✓ Kategori already exists: " . $kategoriNama . " (ID: " . $kategori->id . ")\n";
        }
        
        // Update jabatan with unique kategori_id
        $jabatan->kategori_id = $kategori->id;
        $jabatan->save();
        echo "    ✓ Updated jabatan kategori_id to: " . $kategori->id . "\n";
        
        // Update pegawais for this jabatan
        $pegawais = App\Models\Pegawai::where('user_id', $user->id)
            ->where('jabatan_id', $jabatan->id)
            ->get();
        
        foreach ($pegawais as $pegawai) {
            $pegawai->kategori_id = $kategori->id;
            $pegawai->save();
            echo "    ✓ Updated pegawai " . $pegawai->nama . " kategori_id to: " . $kategori->id . "\n";
        }
        
        echo "\n";
    }
}

echo "Fixing jabatan ID sequence per user...\n";

// For each user, ensure jabatan IDs are sequential starting from 1
foreach ($users as $user) {
    echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
    
    $jabatans = App\Models\Jabatan::where('user_id', $user->id)
        ->orderBy('id', 'asc')
        ->get();
    
    $expectedId = 1;
    
    foreach ($jabatans as $jabatan) {
        if ($jabatan->id != $expectedId) {
            echo "  Jabatan " . $jabatan->nama . " has ID " . $jabatan->id . ", expected " . $expectedId . "\n";
            
            // This is complex to fix due to foreign key constraints
            // For now, we'll just note the issue
            echo "  ⚠️  ID sequence issue detected (requires manual fixing)\n";
        }
        $expectedId++;
    }
    
    echo "  Total jabatans: " . $jabatans->count() . "\n\n";
}

echo "Done! Each jabatan now has unique kategori_id.\n";

echo "\n=== VERIFICATION ===\n";
$jabatans = App\Models\Jabatan::where('user_id', 1)->get();

foreach ($jabatans as $jabatan) {
    $pegawaiCount = App\Models\Pegawai::where('user_id', 1)
        ->where('jabatan_id', $jabatan->id)
        ->count();
    
    echo "Jabatan: " . $jabatan->nama . " (ID: " . $jabatan->id . ")\n";
    echo "  Kategori ID: " . $jabatan->kategori_id . "\n";
    echo "  Pegawai count: " . $pegawaiCount . "\n";
    echo "\n";
}

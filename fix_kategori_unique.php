<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing kategori_pegawai with unique names per user...\n\n";

// Get all users
$users = App\Models\User::all();

foreach ($users as $user) {
    echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
    
    // Create unique BTKL kategori for this user
    $btklKategoriName = 'BTKL - ' . $user->name;
    
    $btklKategori = App\Models\KategoriPegawai::where('user_id', $user->id)
        ->where('nama', $btklKategoriName)
        ->first();
    
    if (!$btklKategori) {
        $btklKategori = new App\Models\KategoriPegawai();
        $btklKategori->user_id = $user->id;
        $btklKategori->nama = $btklKategoriName;
        $btklKategori->deskripsi = 'Tenaga Kerja Langsung untuk ' . $user->name;
        $btklKategori->save();
        echo "  ✓ Created BTKL kategori: " . $btklKategoriName . "\n";
    } else {
        echo "  ✓ BTKL kategori already exists: " . $btklKategoriName . "\n";
    }
    
    // Create unique BTKTL kategori for this user
    $btktlKategoriName = 'BTKTL - ' . $user->name;
    
    $btktlKategori = App\Models\KategoriPegawai::where('user_id', $user->id)
        ->where('nama', $btktlKategoriName)
        ->first();
    
    if (!$btktlKategori) {
        $btktlKategori = new App\Models\KategoriPegawai();
        $btktlKategori->user_id = $user->id;
        $btktlKategori->nama = $btktlKategoriName;
        $btktlKategori->deskripsi = 'Bukan Tenaga Kerja Langsung untuk ' . $user->name;
        $btktlKategori->save();
        echo "  ✓ Created BTKTL kategori: " . $btktlKategoriName . "\n";
    } else {
        echo "  ✓ BTKTL kategori already exists: " . $btktlKategoriName . "\n";
    }
    
    // Update existing jabatans to use the new kategori
    $jabatans = App\Models\Jabatan::where('user_id', $user->id)->get();
    
    foreach ($jabatans as $jabatan) {
        if ($jabatan->kategori === 'btkl') {
            $jabatan->kategori_id = $btklKategori->id;
            $jabatan->save();
            echo "  ✓ Updated jabatan '" . $jabatan->nama . "' to use kategori: " . $btklKategoriName . "\n";
        } elseif ($jabatan->kategori === 'btktl') {
            $jabatan->kategori_id = $btktlKategori->id;
            $jabatan->save();
            echo "  ✓ Updated jabatan '" . $jabatan->nama . "' to use kategori: " . $btktlKategoriName . "\n";
        }
    }
    
    echo "\n";
}

echo "Done! All users now have unique kategoris.\n";

echo "\n=== VERIFICATION ===\n";
$kategoris = App\Models\KategoriPegawai::orderBy('user_id')->orderBy('nama')->get();

foreach ($kategoris as $kategori) {
    $userName = $kategori->user ? $kategori->user->name : 'Unknown';
    echo "Kategori: " . $kategori->nama . " (ID: " . $kategori->id . ") - User: " . $userName . " (ID: " . $kategori->user_id . ")\n";
}

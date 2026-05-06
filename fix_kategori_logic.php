<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing kategori_pegawai logic for proper multi-tenant...\n\n";

// Step 1: Clean up duplicate and incorrect kategori entries
echo "=== STEP 1: Cleaning up incorrect kategori entries ===\n";

// Remove user-specific kategoris (Pengukusan - MUHAMMAD ARKAN ABIYYU, etc.)
// These should be handled differently - they should not be in kategori_pegawai table
$userSpecificKategoris = App\Models\KategoriPegawai::where('nama', 'like', '% - %')
    ->where(function($query) {
        $query->where('nama', 'like', '%Pengukusan%')
              ->orWhere('nama', 'like', '%Pengemasan%');
    })
    ->get();

foreach ($userSpecificKategoris as $kategori) {
    echo "Removing user-specific kategori: " . $kategori->nama . " (ID: " . $kategori->id . ")\n";
    
    // Update any jabatans that use this kategori_id to NULL first
    $affectedJabatans = App\Models\Jabatan::where('kategori_id', $kategori->id)->get();
    foreach ($affectedJabatans as $jabatan) {
        $jabatan->kategori_id = null;
        $jabatan->save();
        echo "  - Updated jabatan '" . $jabatan->nama . "' to NULL kategori_id\n";
    }
    
    // Delete the kategori
    $kategori->delete();
    echo "  ✓ Deleted kategori\n\n";
}

// Step 2: Keep only proper BTKL/BTKTL kategoris for users who actually created them
echo "=== STEP 2: Cleaning up BTKL/BTKTL kategoris ===\n";

// Get the original BTKL and BTKTL (should belong to admin user 1)
$originalBTKL = App\Models\KategoriPegawai::where('nama', 'BTKL')->where('user_id', 1)->first();
$originalBTKTL = App\Models\KategoriPegawai::where('nama', 'BTKTL')->where('user_id', 1)->first();

if ($originalBTKL) {
    echo "Keeping original BTKL (ID: " . $originalBTKL->id . ") for user_id: 1\n";
} else {
    echo "Creating original BTKL for admin user (user_id: 1)\n";
    $originalBTKL = new App\Models\KategoriPegawai();
    $originalBTKL->user_id = 1;
    $originalBTKL->nama = 'BTKL';
    $originalBTKL->deskripsi = 'Tenaga Kerja Langsung';
    $originalBTKL->save();
}

if ($originalBTKTL) {
    echo "Keeping original BTKTL (ID: " . $originalBTKTL->id . ") for user_id: 1\n";
} else {
    echo "Creating original BTKTL for admin user (user_id: 1)\n";
    $originalBTKTL = new App\Models\KategoriPegawai();
    $originalBTKTL->user_id = 1;
    $originalBTKTL->nama = 'BTKTL';
    $originalBTKTL->deskripsi = 'Bukan Tenaga Kerja Langsung';
    $originalBTKTL->save();
}

// Remove user-specific BTKL/BTKTL kategoris (BTKL - Budi Susanto, etc.)
// These should not exist - users should use the original BTKL/BTKTL
$userSpecificBTKL = App\Models\KategoriPegawai::where('nama', 'like', 'BTKL - %')->get();
$userSpecificBTKTL = App\Models\KategoriPegawai::where('nama', 'like', 'BTKTL - %')->get();

foreach ($userSpecificBTKL as $kategori) {
    echo "Removing user-specific BTKL: " . $kategori->nama . " (ID: " . $kategori->id . ")\n";
    
    // Update jabatans to use original BTKL
    $affectedJabatans = App\Models\Jabatan::where('kategori_id', $kategori->id)->get();
    foreach ($affectedJabatans as $jabatan) {
        $jabatan->kategori_id = $originalBTKL->id;
        $jabatan->save();
        echo "  - Updated jabatan '" . $jabatan->nama . "' to use original BTKL (ID: " . $originalBTKL->id . ")\n";
    }
    
    $kategori->delete();
    echo "  ✓ Deleted kategori\n";
}

foreach ($userSpecificBTKTL as $kategori) {
    echo "Removing user-specific BTKTL: " . $kategori->nama . " (ID: " . $kategori->id . ")\n";
    
    // Update jabatans to use original BTKTL
    $affectedJabatans = App\Models\Jabatan::where('kategori_id', $kategori->id)->get();
    foreach ($affectedJabatans as $jabatan) {
        $jabatan->kategori_id = $originalBTKTL->id;
        $jabatan->save();
        echo "  - Updated jabatan '" . $jabatan->nama . "' to use original BTKTL (ID: " . $originalBTKTL->id . ")\n";
    }
    
    $kategori->delete();
    echo "  ✓ Deleted kategori\n";
}

// Step 3: Update jabatans to use correct kategori based on their 'kategori' field
echo "\n=== STEP 3: Updating jabatans with correct kategori_id ===\n";

$jabatans = App\Models\Jabatan::whereNull('kategori_id')->get();

foreach ($jabatans as $jabatan) {
    echo "Processing jabatan: " . $jabatan->nama . " (kategori: " . $jabatan->kategori . ")\n";
    
    if ($jabatan->kategori === 'btkl') {
        $jabatan->kategori_id = $originalBTKL->id;
        $jabatan->save();
        echo "  ✓ Set kategori_id to BTKL (ID: " . $originalBTKL->id . ")\n";
    } elseif ($jabatan->kategori === 'btktl') {
        $jabatan->kategori_id = $originalBTKTL->id;
        $jabatan->save();
        echo "  ✓ Set kategori_id to BTKTL (ID: " . $originalBTKTL->id . ")\n";
    } else {
        echo "  ⚠️  Unknown kategori: " . $jabatan->kategori . "\n";
    }
}

echo "\nDone! Kategori logic has been fixed.\n";

echo "\n=== VERIFICATION ===\n";
$kategoris = App\Models\KategoriPegawai::orderBy('user_id')->orderBy('nama')->get();

foreach ($kategoris as $kategori) {
    $userName = $kategori->user ? $kategori->user->name : 'Unknown';
    echo "Kategori: " . $kategori->nama . " (ID: " . $kategori->id . ") - User: " . $userName . " (ID: " . $kategori->user_id . ")\n";
}

echo "\n=== JABATANS VERIFICATION ===\n";
$jabatans = App\Models\Jabatan::where('user_id', 1)->get();

foreach ($jabatans as $jabatan) {
    $kategoriName = $jabatan->kategoriPegawai ? $jabatan->kategoriPegawai->nama : 'NULL';
    echo "Jabatan: " . $jabatan->nama . " - Kategori: " . $kategoriName . " (ID: " . $jabatan->kategori_id . ")\n";
}

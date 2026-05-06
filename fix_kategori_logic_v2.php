<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing kategori_pegawai logic for proper multi-tenant (v2)...\n\n";

// Step 1: First, update all pegawais to NULL kategori_id to avoid foreign key issues
echo "=== STEP 1: Updating pegawais to NULL kategori_id ===\n";

$userSpecificKategoris = App\Models\KategoriPegawai::where('nama', 'like', '% - %')
    ->where(function($query) {
        $query->where('nama', 'like', '%Pengukusan%')
              ->orWhere('nama', 'like', '%Pengemasan%');
    })
    ->get();

foreach ($userSpecificKategoris as $kategori) {
    echo "Processing kategori: " . $kategori->nama . " (ID: " . $kategori->id . ")\n";
    
    // Update pegawais first
    $pegawais = App\Models\Pegawai::where('kategori_id', $kategori->id)->get();
    foreach ($pegawais as $pegawai) {
        $pegawai->kategori_id = null;
        $pegawai->save();
        echo "  - Updated pegawai '" . $pegawai->nama . "' to NULL kategori_id\n";
    }
    
    // Update jabatans
    $jabatans = App\Models\Jabatan::where('kategori_id', $kategori->id)->get();
    foreach ($jabatans as $jabatan) {
        $jabatan->kategori_id = null;
        $jabatan->save();
        echo "  - Updated jabatan '" . $jabatan->nama . "' to NULL kategori_id\n";
    }
    
    echo "  ✓ Ready to delete kategori\n\n";
}

// Step 2: Update user-specific BTKL/BTKTL kategoris
echo "=== STEP 2: Processing user-specific BTKL/BTKTL kategoris ===\n";

// Get the original BTKL and BTKTL (should belong to admin user 1)
$originalBTKL = App\Models\KategoriPegawai::where('nama', 'BTKL')->where('user_id', 1)->first();
$originalBTKTL = App\Models\KategoriPegawai::where('nama', 'BTKTL')->where('user_id', 1)->first();

if ($originalBTKL) {
    echo "Found original BTKL (ID: " . $originalBTKL->id . ") for user_id: 1\n";
} else {
    echo "Creating original BTKL for admin user (user_id: 1)\n";
    $originalBTKL = new App\Models\KategoriPegawai();
    $originalBTKL->user_id = 1;
    $originalBTKL->nama = 'BTKL';
    $originalBTKL->deskripsi = 'Tenaga Kerja Langsung';
    $originalBTKL->save();
    echo "  ✓ Created BTKL (ID: " . $originalBTKL->id . ")\n";
}

if ($originalBTKTL) {
    echo "Found original BTKTL (ID: " . $originalBTKTL->id . ") for user_id: 1\n";
} else {
    echo "Creating original BTKTL for admin user (user_id: 1)\n";
    $originalBTKTL = new App\Models\KategoriPegawai();
    $originalBTKTL->user_id = 1;
    $originalBTKTL->nama = 'BTKTL';
    $originalBTKTL->deskripsi = 'Bukan Tenaga Kerja Langsung';
    $originalBTKTL->save();
    echo "  ✓ Created BTKTL (ID: " . $originalBTKTL->id . ")\n";
}

// Process user-specific BTKL kategoris
$userSpecificBTKL = App\Models\KategoriPegawai::where('nama', 'like', 'BTKL - %')->get();
foreach ($userSpecificBTKL as $kategori) {
    echo "Processing user-specific BTKL: " . $kategori->nama . " (ID: " . $kategori->id . ")\n";
    
    // Update pegawais to use original BTKL
    $pegawais = App\Models\Pegawai::where('kategori_id', $kategori->id)->get();
    foreach ($pegawais as $pegawai) {
        $pegawai->kategori_id = $originalBTKL->id;
        $pegawai->save();
        echo "  - Updated pegawai '" . $pegawai->nama . "' to use original BTKL (ID: " . $originalBTKL->id . ")\n";
    }
    
    // Update jabatans to use original BTKL
    $jabatans = App\Models\Jabatan::where('kategori_id', $kategori->id)->get();
    foreach ($jabatans as $jabatan) {
        $jabatan->kategori_id = $originalBTKL->id;
        $jabatan->save();
        echo "  - Updated jabatan '" . $jabatan->nama . "' to use original BTKL (ID: " . $originalBTKL->id . ")\n";
    }
    
    echo "  ✓ Ready to delete kategori\n";
}

// Process user-specific BTKTL kategoris
$userSpecificBTKTL = App\Models\KategoriPegawai::where('nama', 'like', 'BTKTL - %')->get();
foreach ($userSpecificBTKTL as $kategori) {
    echo "Processing user-specific BTKTL: " . $kategori->nama . " (ID: " . $kategori->id . ")\n";
    
    // Update pegawais to use original BTKTL
    $pegawais = App\Models\Pegawai::where('kategori_id', $kategori->id)->get();
    foreach ($pegawais as $pegawai) {
        $pegawai->kategori_id = $originalBTKTL->id;
        $pegawai->save();
        echo "  - Updated pegawai '" . $pegawai->nama . "' to use original BTKTL (ID: " . $originalBTKTL->id . ")\n";
    }
    
    // Update jabatans to use original BTKTL
    $jabatans = App\Models\Jabatan::where('kategori_id', $kategori->id)->get();
    foreach ($jabatans as $jabatan) {
        $jabatan->kategori_id = $originalBTKTL->id;
        $jabatan->save();
        echo "  - Updated jabatan '" . $jabatan->nama . "' to use original BTKTL (ID: " . $originalBTKTL->id . ")\n";
    }
    
    echo "  ✓ Ready to delete kategori\n";
}

// Step 3: Now safely delete all user-specific kategoris
echo "\n=== STEP 3: Deleting user-specific kategoris ===\n";

$allUserSpecific = App\Models\KategoriPegawai::where('nama', 'like', '% - %')->get();
foreach ($allUserSpecific as $kategori) {
    echo "Deleting: " . $kategori->nama . " (ID: " . $kategori->id . ")\n";
    $kategori->delete();
    echo "  ✓ Deleted\n";
}

// Step 4: Update remaining jabatans based on their kategori field
echo "\n=== STEP 4: Updating jabatans based on kategori field ===\n";

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

echo "\nDone! Kategori logic has been fixed properly.\n";

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

echo "\n=== PEGAWAIS VERIFICATION ===\n";
$pegawais = App\Models\Pegawai::where('user_id', 1)->get();

foreach ($pegawais as $pegawai) {
    $kategoriName = $pegawai->kategoriPegawai ? $pegawai->kategoriPegawai->nama : 'NULL';
    echo "Pegawai: " . $pegawai->nama . " - Kategori: " . $kategoriName . " (ID: " . $pegawai->kategori_id . ")\n";
}

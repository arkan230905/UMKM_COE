<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing kategori_pegawai table with proper user_id for multi-tenant...\n\n";

// Get all kategoris
$kategoris = App\Models\KategoriPegawai::whereNull('user_id')->get();

foreach ($kategoris as $kategori) {
    echo "Processing kategori: " . $kategori->nama . " (ID: " . $kategori->id . ")\n";
    
    if ($kategori->nama === 'BTKL') {
        // Assign to user_id = 1 (default admin user)
        $kategori->user_id = 1;
        $kategori->save();
        echo "  ✓ Assigned to user_id: 1 (BTKL - General)\n";
    } elseif ($kategori->nama === 'BTKTL') {
        // Assign to user_id = 1 (default admin user)
        $kategori->user_id = 1;
        $kategori->save();
        echo "  ✓ Assigned to user_id: 1 (BTKTL - General)\n";
    } elseif (strpos($kategori->nama, 'MUHAMMAD ARKAN ABIYYU') !== false) {
        // Assign to user_id = 1 (MUHAMMAD ARKAN ABIYYU)
        $kategori->user_id = 1;
        $kategori->save();
        echo "  ✓ Assigned to user_id: 1 (User-specific)\n";
    } else {
        echo "  ⚠️  Unknown kategori, assigning to user_id: 1\n";
        $kategori->user_id = 1;
        $kategori->save();
    }
}

echo "\nCreating default kategoris for all users...\n";

// Get all users
$users = App\Models\User::all();

foreach ($users as $user) {
    echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
    
    // Check if user already has BTKL kategori
    $btklKategori = App\Models\KategoriPegawai::where('user_id', $user->id)
        ->where('nama', 'BTKL')
        ->first();
    
    if (!$btklKategori) {
        $btklKategori = new App\Models\KategoriPegawai();
        $btklKategori->user_id = $user->id;
        $btklKategori->nama = 'BTKL';
        $btklKategori->deskripsi = 'Tenaga Kerja Langsung untuk ' . $user->name;
        $btklKategori->save();
        echo "  ✓ Created BTKL kategori for user " . $user->name . "\n";
    } else {
        echo "  ✓ BTKL kategori already exists for user " . $user->name . "\n";
    }
    
    // Check if user already has BTKTL kategori
    $btktlKategori = App\Models\KategoriPegawai::where('user_id', $user->id)
        ->where('nama', 'BTKTL')
        ->first();
    
    if (!$btktlKategori) {
        $btktlKategori = new App\Models\KategoriPegawai();
        $btktlKategori->user_id = $user->id;
        $btktlKategori->nama = 'BTKTL';
        $btktlKategori->deskripsi = 'Bukan Tenaga Kerja Langsung untuk ' . $user->name;
        $btktlKategori->save();
        echo "  ✓ Created BTKTL kategori for user " . $user->name . "\n";
    } else {
        echo "  ✓ BTKTL kategori already exists for user " . $user->name . "\n";
    }
    
    echo "\n";
}

echo "Done! All kategoris now have proper user_id.\n";

echo "\n=== VERIFICATION ===\n";
$kategoris = App\Models\KategoriPegawai::orderBy('user_id')->orderBy('nama')->get();

foreach ($kategoris as $kategori) {
    $userName = $kategori->user ? $kategori->user->name : 'Unknown';
    echo "Kategori: " . $kategori->nama . " (ID: " . $kategori->id . ") - User: " . $userName . " (ID: " . $kategori->user_id . ")\n";
}

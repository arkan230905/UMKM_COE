<?php
// Check pegawai jabatan link

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Pegawai;
use App\Models\Jabatan;

echo "=== CHECK PEGAWAI JABATAN LINK ===\n\n";

// Login as User 13
$user = User::find(13);
auth()->login($user);

echo "Logged in as: {$user->name} (ID: {$user->id})\n\n";

// Get all pegawai
$pegawais = Pegawai::all();

echo "All Pegawai for this user:\n";
echo "─────────────────────────────────────────\n";

foreach ($pegawais as $pegawai) {
    echo "\nPegawai: {$pegawai->nama} (ID: {$pegawai->id})\n";
    echo "  ├─ Jabatan ID: {$pegawai->jabatan_id}\n";
    
    if (!$pegawai->jabatan_id) {
        echo "  ├─ ❌ NO JABATAN ID!\n";
        continue;
    }
    
    // Try to load jabatan
    $jabatan = Jabatan::find($pegawai->jabatan_id);
    
    if (!$jabatan) {
        echo "  ├─ ❌ Jabatan ID {$pegawai->jabatan_id} not found!\n";
        continue;
    }
    
    echo "  ├─ Jabatan: {$jabatan->nama}\n";
    echo "  ├─ Tunjangan Transport: " . number_format($jabatan->tunjangan_transport, 0, ',', '.') . "\n";
    echo "  ├─ Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi, 0, ',', '.') . "\n";
    
    // Try with eager loading
    $pegawaiWithJabatan = Pegawai::with('jabatanRelasi')->find($pegawai->id);
    
    if ($pegawaiWithJabatan->jabatanRelasi) {
        echo "  ├─ ✓ Eager loading works\n";
        echo "  └─ Tunjangan Transport (via relation): " . number_format($pegawaiWithJabatan->jabatanRelasi->tunjangan_transport, 0, ',', '.') . "\n";
    } else {
        echo "  └─ ❌ Eager loading FAILED - jabatanRelasi is NULL!\n";
    }
}

echo "\n=== END CHECK ===\n";

<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DEBUGGING PENGGAJIAN TUNJANGAN ISSUE FOR HOSTING\n";

// Get Budi Susanto employee
$pegawai = \App\Models\Pegawai::where('nama', 'Budi Susanto')->first();

if (!$pegawai) {
    echo "ERROR: Pegawai Budi Susanto not found!\n";
    exit;
}

echo "\n=== PEGAWAI DATA ===\n";
echo "ID: " . $pegawai->id . "\n";
echo "Nama: " . $pegawai->nama . "\n";
echo "Jabatan ID: " . ($pegawai->jabatan_id ?? 'NULL') . "\n";
echo "User ID: " . ($pegawai->user_id ?? 'NULL') . "\n";

// Check jabatan data
if ($pegawai->jabatan_id) {
    $jabatan = \App\Models\Jabatan::find($pegawai->jabatan_id);
    if ($jabatan) {
        echo "\n=== JABATAN DATA ===\n";
        echo "Jabatan ID: " . $jabatan->id . "\n";
        echo "Nama Jabatan: " . $jabatan->nama_jabatan . "\n";
        echo "Tunjangan Jabatan: Rp " . number_format($jabatan->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Transport: Rp " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Konsumsi: Rp " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        echo "User ID: " . ($jabatan->user_id ?? 'NULL') . "\n";
    } else {
        echo "\nERROR: Jabatan not found for ID " . $pegawai->jabatan_id . "\n";
    }
} else {
    echo "\nERROR: Pegawai has no jabatan_id\n";
}

// Check relationship loading
echo "\n=== RELATIONSHIP TEST ===\n";
$pegawaiWithJabatan = \App\Models\Pegawai::with('jabatanRelasi')->find($pegawai->id);
echo "Pegawai with jabatan loaded: " . ($pegawaiWithJabatan ? "YES" : "NO") . "\n";

if ($pegawaiWithJabatan && $pegawaiWithJabatan->jabatanRelasi) {
    echo "Jabatan from relationship: " . $pegawaiWithJabatan->jabatanRelasi->nama_jabatan . "\n";
    echo "Tunjangan from relationship: Rp " . number_format($pegawaiWithJabatan->jabatanRelasi->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
} else {
    echo "Jabatan relationship not working!\n";
}

// Check if there are any multi-tenant filtering issues
echo "\n=== MULTI-TENANT CHECK ===\n";

// Check jabatans for user_id = 1
$jabatansForUser = \App\Models\Jabatan::where('user_id', 1)->get();
echo "Total jabatans for user_id=1: " . $jabatansForUser->count() . "\n";

foreach ($jabatansForUser as $jabatan) {
    echo "  - " . $jabatan->nama_jabatan . " (ID: " . $jabatan->id . ")\n";
    echo "    Tunjangan: Rp " . number_format($jabatan->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
}

// Check if the specific jabatan exists for user_id = 1
if ($pegawai->jabatan_id) {
    $pegawaiJabatanForUser = \App\Models\Jabatan::where('id', $pegawai->jabatan_id)
        ->where('user_id', 1)
        ->first();
    
    echo "\nJabatan check for user_id=1: " . ($pegawaiJabatanForUser ? "FOUND" : "NOT FOUND") . "\n";
    
    if ($pegawaiJabatanForUser) {
        echo "Tunjangan for user_id=1: Rp " . number_format($pegawaiJabatanForUser->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
    }
}

// Check PenggajianController logic simulation
echo "\n=== SIMULATING CONTROLLER LOGIC ===\n";

// Simulate what the controller might be doing
$pegawaiForController = \App\Models\Pegawai::where('user_id', 1)
    ->with('jabatanRelasi')
    ->where('id', $pegawai->id)
    ->first();

echo "Pegawai found by controller: " . ($pegawaiForController ? "YES" : "NO") . "\n";

if ($pegawaiForController) {
    echo "Jabatan loaded by controller: " . ($pegawaiForController->jabatanRelasi ? "YES" : "NO") . "\n";
    
    if ($pegawaiForController->jabatanRelasi) {
        $tunjanganJabatan = $pegawaiForController->jabatanRelasi->tunjangan_jabatan ?? 0;
        $tunjanganTransport = $pegawaiForController->jabatanRelasi->tunjangan_transport ?? 0;
        $tunjanganKonsumsi = $pegawaiForController->jabatanRelasi->tunjangan_konsumsi ?? 0;
        
        echo "Calculated tunjangan:\n";
        echo "  Jabatan: Rp " . number_format($tunjanganJabatan, 0, ',', '.') . "\n";
        echo "  Transport: Rp " . number_format($tunjanganTransport, 0, ',', '.') . "\n";
        echo "  Konsumsi: Rp " . number_format($tunjanganKonsumsi, 0, ',', '.') . "\n";
        echo "  Total: Rp " . number_format($tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi, 0, ',', '.') . "\n";
    }
}

echo "\n=== DIAGNOSIS ===\n";
if (!$pegawai->jabatan_id) {
    echo "ISSUE: Pegawai has no jabatan_id\n";
} elseif (!$pegawai->jabatanRelasi) {
    echo "ISSUE: Jabatan relationship not loading\n";
} elseif (!($pegawai->jabatanRelasi->tunjangan_jabatan ?? 0)) {
    echo "ISSUE: Tunjangan jabatan is zero or null\n";
} else {
    echo "ISSUE: Likely multi-tenant filtering in controller\n";
}

echo "\nDebug completed!\n";

<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DEBUGGING DEDI GUNAWAN PENGGAJIAN ISSUE FOR HOSTING\n";

// Get Dedi Gunawan employee
$pegawai = \App\Models\Pegawai::where('nama', 'Dedi Gunawan')->first();

if (!$pegawai) {
    echo "ERROR: Pegawai Dedi Gunawan not found!\n";
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
        echo "Nama Jabatan: " . $jabatan->nama . "\n";
        echo "Tarif: Rp " . number_format($jabatan->tarif ?? 0, 0, ',', '.') . "\n";
        echo "Tarif per Jam: Rp " . number_format($jabatan->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Jabatan: Rp " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.') . "\n";
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
    echo "Jabatan from relationship: " . $pegawaiWithJabatan->jabatanRelasi->nama . "\n";
    echo "Tarif from relationship: Rp " . number_format($pegawaiWithJabatan->jabatanRelasi->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan from relationship: Rp " . number_format($pegawaiWithJabatan->jabatanRelasi->tunjangan ?? 0, 0, ',', '.') . "\n";
} else {
    echo "Jabatan relationship not working!\n";
}

// Check if there are any multi-tenant filtering issues
echo "\n=== MULTI-TENANT CHECK ===\n";

// Check jabatans for user_id = 1
$jabatansForUser = \App\Models\Jabatan::where('user_id', 1)->get();
echo "Total jabatans for user_id=1: " . $jabatansForUser->count() . "\n";

foreach ($jabatansForUser as $jabatan) {
    echo "  - " . $jabatan->nama . " (ID: " . $jabatan->id . ")\n";
    echo "    Tarif: Rp " . number_format($jabatan->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
    echo "    Tunjangan: Rp " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.') . "\n";
}

// Check if the specific jabatan exists for user_id = 1
if ($pegawai->jabatan_id) {
    $pegawaiJabatanForUser = \App\Models\Jabatan::where('id', $pegawai->jabatan_id)
        ->where('user_id', 1)
        ->first();
    
    echo "\nJabatan check for user_id=1: " . ($pegawaiJabatanForUser ? "FOUND" : "NOT FOUND") . "\n";
    
    if ($pegawaiJabatanForUser) {
        echo "Tarif for user_id=1: Rp " . number_format($pegawaiJabatanForUser->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan for user_id=1: Rp " . number_format($pegawaiJabatanForUser->tunjangan ?? 0, 0, ',', '.') . "\n";
    }
}

// Check kualifikasi-tenaga-kerja data as requested
echo "\n=== CHECKING KUALIFIKASI TENAGA KERJA ===\n";
$kualifikasi = \Illuminate\Support\Facades\DB::table('kualifikasi_tenaga_kerja')->get();
echo "Total kualifikasi records: " . $kualifikasi->count() . "\n";

foreach ($kualifikasi as $k) {
    echo "ID: " . $k->id . ", Nama: " . ($k->nama ?? 'NULL') . ", Tarif: " . number_format($k->tarif ?? 0, 0, ',', '.') . "\n";
}

// Check if there's a relationship between jabatan and kualifikasi
echo "\n=== CHECKING JABATAN-KUALIFIKASI RELATIONSHIP ===\n";
if ($pegawai->jabatan_id) {
    $jabatan = \App\Models\Jabatan::find($pegawai->jabatan_id);
    if ($jabatan) {
        echo "Jabatan kategori_id: " . ($jabatan->kategori_id ?? 'NULL') . "\n";
        
        if ($jabatan->kategori_id) {
            $kategori = \Illuminate\Support\Facades\DB::table('kualifikasi_tenaga_kerja')->find($jabatan->kategori_id);
            if ($kategori) {
                echo "Kategori found: " . ($kategori->nama ?? 'NULL') . "\n";
                echo "Kategori tarif: Rp " . number_format($kategori->tarif ?? 0, 0, ',', '.') . "\n";
            } else {
                echo "Kategori not found for ID " . $jabatan->kategori_id . "\n";
            }
        }
    }
}

// Simulate PenggajianController logic
echo "\n=== SIMULATING CONTROLLER LOGIC ===\n";
$pegawaiForController = \App\Models\Pegawai::where('user_id', 1)
    ->with('jabatanRelasi')
    ->where('id', $pegawai->id)
    ->first();

echo "Pegawai found by controller: " . ($pegawaiForController ? "YES" : "NO") . "\n";

if ($pegawaiForController) {
    echo "Jabatan loaded by controller: " . ($pegawaiForController->jabatanRelasi ? "YES" : "NO") . "\n";
    
    if ($pegawaiForController->jabatanRelasi) {
        $tarif = $pegawaiForController->jabatanRelasi->tarif_per_jam ?? $pegawaiForController->jabatanRelasi->tarif ?? 0;
        $tunjanganJabatan = $pegawaiForController->jabatanRelasi->tunjangan ?? 0;
        $tunjanganTransport = $pegawaiForController->jabatanRelasi->tunjangan_transport ?? 0;
        $tunjanganKonsumsi = $pegawaiForController->jabatanRelasi->tunjangan_konsumsi ?? 0;
        
        echo "Calculated values:\n";
        echo "  Tarif: Rp " . number_format($tarif, 0, ',', '.') . "\n";
        echo "  Tunjangan Jabatan: Rp " . number_format($tunjanganJabatan, 0, ',', '.') . "\n";
        echo "  Tunjangan Transport: Rp " . number_format($tunjanganTransport, 0, ',', '.') . "\n";
        echo "  Tunjangan Konsumsi: Rp " . number_format($tunjanganKonsumsi, 0, ',', '.') . "\n";
        echo "  Total Tunjangan: Rp " . number_format($tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi, 0, ',', '.') . "\n";
    }
}

echo "\n=== DIAGNOSIS ===\n";
if (!$pegawai->jabatan_id) {
    echo "ISSUE: Pegawai has no jabatan_id\n";
} elseif (!$pegawai->jabatanRelasi) {
    echo "ISSUE: Jabatan relationship not loading\n";
} elseif (!($pegawai->jabatanRelasi->tarif_per_jam ?? $pegawai->jabatanRelasi->tarif ?? 0)) {
    echo "ISSUE: Tarif is 0\n";
} elseif (!($pegawai->jabatanRelasi->tunjangan ?? 0)) {
    echo "ISSUE: Tunjangan jabatan is 0\n";
} else {
    echo "ISSUE: Likely controller logic or kualifikasi relationship\n";
}

echo "\nDebug completed!\n";

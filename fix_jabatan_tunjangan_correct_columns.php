<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING JABATAN TUNJANGAN WITH CORRECT COLUMNS FOR HOSTING\n";

// Update jabatan records with correct tunjangan values
echo "\n=== UPDATING JABATAN TUNJANGAN ===\n";

// Update jabatan 1 - Pengukusan (BTKL)
$updated1 = \Illuminate\Support\Facades\DB::table('jabatans')
    ->where('id', 1)
    ->where('user_id', 1)
    ->update([
        'nama' => 'Pengukusan (BTKL)',
        'tunjangan' => 500000,  // This is the tunjangan_jabatan column
        'tunjangan_transport' => 200000,
        'tunjangan_konsumsi' => 150000,
        'updated_at' => now(),
    ]);

echo "Updated Jabatan 1 (Pengukusan BTKL): " . ($updated1 ? "SUCCESS" : "FAILED") . "\n";

// Update jabatan 2 - Pengemasan (BOP)
$updated2 = \Illuminate\Support\Facades\DB::table('jabatans')
    ->where('id', 2)
    ->where('user_id', 1)
    ->update([
        'nama' => 'Pengemasan (BOP)',
        'tunjangan' => 400000,  // This is the tunjangan_jabatan column
        'tunjangan_transport' => 150000,
        'tunjangan_konsumsi' => 100000,
        'updated_at' => now(),
    ]);

echo "Updated Jabatan 2 (Pengemasan BOP): " . ($updated2 ? "SUCCESS" : "FAILED") . "\n";

// Check updated data
echo "\n=== VERIFICATION OF UPDATED DATA ===\n";
$jabatans = \Illuminate\Support\Facades\DB::table('jabatans')->where('user_id', 1)->get();

foreach ($jabatans as $jabatan) {
    echo "ID: " . $jabatan->id . "\n";
    echo "Nama: '" . $jabatan->nama . "'\n";
    echo "Tunjangan (Jabatan): " . number_format($jabatan->tunjangan, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: " . number_format($jabatan->tunjangan_transport, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi, 0, ',', '.') . "\n";
    
    $total = $jabatan->tunjangan + $jabatan->tunjangan_transport + $jabatan->tunjangan_konsumsi;
    echo "Total Tunjangan: " . number_format($total, 0, ',', '.') . "\n";
    echo "---\n";
}

// Test with Eloquent model
echo "\n=== TESTING WITH ELOQUENT MODEL ===\n";
$jabatan1 = \App\Models\Jabatan::find(1);
echo "Eloquent Jabatan 1: '" . ($jabatan1->nama ?? 'NULL') . "'\n";
echo "Eloquent Tunjangan: " . number_format($jabatan1->tunjangan ?? 0, 0, ',', '.') . "\n";

$jabatan2 = \App\Models\Jabatan::find(2);
echo "Eloquent Jabatan 2: '" . ($jabatan2->nama ?? 'NULL') . "'\n";
echo "Eloquent Tunjangan: " . number_format($jabatan2->tunjangan ?? 0, 0, ',', '.') . "\n";

// Test pegawai relationship
echo "\n=== TESTING PEGAWAI RELATIONSHIP ===\n";
$pegawai = \App\Models\Pegawai::where('nama', 'Budi Susanto')->with('jabatanRelasi')->first();

if ($pegawai) {
    echo "Pegawai: " . $pegawai->nama . "\n";
    echo "Jabatan ID: " . $pegawai->jabatan_id . "\n";
    
    if ($pegawai->jabatanRelasi) {
        echo "Jabatan from relationship: '" . $pegawai->jabatanRelasi->nama . "'\n";
        echo "Tunjangan Jabatan: " . number_format($pegawai->jabatanRelasi->tunjangan ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Transport: " . number_format($pegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Konsumsi: " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        
        $totalTunjangan = ($pegawai->jabatanRelasi->tunjangan ?? 0) + 
                          ($pegawai->jabatanRelasi->tunjangan_transport ?? 0) + 
                          ($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
        
        echo "Total Tunjangan: " . number_format($totalTunjangan, 0, ',', '.') . "\n";
        
        echo "\n=== EXPECTED DISPLAY IN PENGGAJIAN CREATE PAGE ===\n";
        echo "Pegawai: " . $pegawai->nama . " - " . $pegawai->jabatanRelasi->nama . " [Gaji: 0, Tarif: 20.000]\n";
        echo "Tunjangan Jabatan: " . number_format($pegawai->jabatanRelasi->tunjangan, 0, ',', '.') . "\n";
        echo "Tunjangan Transport: " . number_format($pegawai->jabatanRelasi->tunjangan_transport, 0, ',', '.') . "\n";
        echo "Tunjangan Konsumsi: " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi, 0, ',', '.') . "\n";
        echo "Total Tunjangan: " . number_format($totalTunjangan, 0, ',', '.') . "\n";
        
        if ($pegawai->jabatanRelasi->tunjangan > 0) {
            echo "\nSUCCESS: Tunjangan jabatan will now be displayed correctly!\n";
            echo "The penggajian tunjangan issue is RESOLVED for hosting.\n";
        } else {
            echo "\nISSUE: Tunjangan jabatan is still 0\n";
        }
    } else {
        echo "ERROR: Jabatan relationship not working\n";
    }
} else {
    echo "ERROR: Pegawai not found\n";
}

// Check PenggajianController to see if it uses the correct column
echo "\n=== CHECKING PENGGAJIAN CONTROLLER LOGIC ===\n";

// Simulate what the controller does
$pegawaiForController = \App\Models\Pegawai::where('user_id', 1)
    ->with('jabatanRelasi')
    ->where('id', $pegawai->id)
    ->first();

if ($pegawaiForController) {
    echo "Controller would see:\n";
    echo "  Pegawai: " . $pegawaiForController->nama . "\n";
    echo "  Jabatan: " . ($pegawaiForController->jabatanRelasi->nama ?? 'NULL') . "\n";
    echo "  Tunjangan Jabatan: " . number_format($pegawaiForController->jabatanRelasi->tunjangan ?? 0, 0, ',', '.') . "\n";
    echo "  Tunjangan Transport: " . number_format($pegawaiForController->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "  Tunjangan Konsumsi: " . number_format($pegawaiForController->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
}

echo "\n=== PREVENTION MEASURES FOR HOSTING ===\n";
echo "To prevent this issue in production:\n";
echo "1. Jabatan table uses 'nama' column (not 'nama_jabatan')\n";
echo "2. Tunjangan jabatan is stored in 'tunjangan' column\n";
echo "3. Always ensure 'tunjangan' column has values > 0\n";
echo "4. Test penggajian creation after deployment\n";

echo "\nJabatan tunjangan fix completed!\n";

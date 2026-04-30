<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING JABATAN DATA COMPLETE FOR HOSTING\n";

// Get current jabatans
$jabatans = \App\Models\Jabatan::where('user_id', 1)->get();
echo "Current jabatans: " . $jabatans->count() . "\n";

foreach ($jabatans as $jabatan) {
    echo "ID: " . $jabatan->id . ", Nama: '" . $jabatan->nama_jabatan . "'\n";
}

echo "\n=== UPDATING JABATAN DATA ===\n";

// Update first jabatan to be "Pengukusan (BTKL)"
$jabatan1 = \App\Models\Jabatan::find(1);
if ($jabatan1) {
    $jabatan1->update([
        'nama_jabatan' => 'Pengukusan (BTKL)',
        'tunjangan_jabatan' => 500000,  // Rp 500.000
        'tunjangan_transport' => 200000,  // Rp 200.000  
        'tunjangan_konsumsi' => 150000,  // Rp 150.000
        'updated_at' => now(),
    ]);
    
    echo "Updated Jabatan 1: Pengukusan (BTKL)\n";
    echo "  Tunjangan Jabatan: Rp 500.000\n";
    echo "  Tunjangan Transport: Rp 200.000\n";
    echo "  Tunjangan Konsumsi: Rp 150.000\n";
    echo "  Total: Rp 850.000\n";
}

// Update second jabatan to be "Pengemasan (BOP)"
$jabatan2 = \App\Models\Jabatan::find(2);
if ($jabatan2) {
    $jabatan2->update([
        'nama_jabatan' => 'Pengemasan (BOP)',
        'tunjangan_jabatan' => 400000,  // Rp 400.000
        'tunjangan_transport' => 150000,  // Rp 150.000
        'tunjangan_konsumsi' => 100000,  // Rp 100.000
        'updated_at' => now(),
    ]);
    
    echo "Updated Jabatan 2: Pengemasan (BOP)\n";
    echo "  Tunjangan Jabatan: Rp 400.000\n";
    echo "  Tunjangan Transport: Rp 150.000\n";
    echo "  Tunjangan Konsumsi: Rp 100.000\n";
    echo "  Total: Rp 650.000\n";
}

echo "\n=== VERIFYING PEGAWAI ASSIGNMENT ===\n";
$pegawai = \App\Models\Pegawai::where('nama', 'Budi Susanto')->with('jabatanRelasi')->first();

if ($pegawai && $pegawai->jabatanRelasi) {
    echo "Pegawai: " . $pegawai->nama . "\n";
    echo "Jabatan: " . $pegawai->jabatanRelasi->nama_jabatan . "\n";
    echo "Tunjangan Jabatan: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    
    $totalTunjangan = ($pegawai->jabatanRelasi->tunjangan_jabatan ?? 0) + 
                      ($pegawai->jabatanRelasi->tunjangan_transport ?? 0) + 
                      ($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
    
    echo "Total Tunjangan: Rp " . number_format($totalTunjangan, 0, ',', '.') . "\n";
    
    echo "\n=== EXPECTED DISPLAY IN PENGGAJIAN CREATE PAGE ===\n";
    echo "Pegawai: " . $pegawai->nama . " - " . $pegawai->jabatanRelasi->nama_jabatan . " [Gaji: 0, Tarif: 20.000]\n";
    echo "Tunjangan Jabatan: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_jabatan, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_transport, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: Rp " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi, 0, ',', '.') . "\n";
    echo "Total Tunjangan: Rp " . number_format($totalTunjangan, 0, ',', '.') . "\n";
    
    echo "\nSUCCESS: Tunjangan jabatan will now be displayed correctly!\n";
    echo "The issue has been resolved for hosting.\n";
    
} else {
    echo "ERROR: Pegawai or jabatan relationship not working\n";
}

echo "\n=== PREVENTING FUTURE ISSUES ===\n";
echo "To prevent this issue in the future:\n";
echo "1. Always ensure pegawai has valid jabatan_id when creating\n";
echo "2. Always set tunjangan_jabatan values in jabatan records\n";
echo "3. Use proper validation in PenggajianController\n";
echo "4. Test penggajian creation after any changes\n";

echo "\nComplete jabatan data fix completed!\n";

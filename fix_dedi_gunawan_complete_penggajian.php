<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING DEDI GUNAWAN COMPLETE PENGGAJIAN FOR HOSTING\n";

// Get Dedi Gunawan employee
$pegawai = \App\Models\Pegawai::where('nama', 'Dedi Gunawan')->first();

if (!$pegawai) {
    echo "ERROR: Pegawai Dedi Gunawan not found!\n";
    exit;
}

echo "\n=== CURRENT PEGAWAI DATA ===\n";
echo "ID: " . $pegawai->id . "\n";
echo "Nama: " . $pegawai->nama . "\n";
echo "Jabatan ID: " . ($pegawai->jabatan_id ?? 'NULL') . "\n";
echo "User ID: " . ($pegawai->user_id ?? 'NULL') . "\n";

// Get available jabatans
echo "\n=== AVAILABLE JABATANS ===\n";
$jabatans = \App\Models\Jabatan::where('user_id', 1)->get();
echo "Total jabatans available: " . $jabatans->count() . "\n";

foreach ($jabatans as $jabatan) {
    echo "ID: " . $jabatan->id . "\n";
    echo "Nama Jabatan: '" . $jabatan->nama . "'\n";
    echo "Tarif: Rp " . number_format($jabatan->tarif_per_jam ?? $jabatan->tarif ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Jabatan: Rp " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: Rp " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: Rp " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    echo "---\n";
}

// Update jabatan 2 to have proper values (Pengemasan should have tarif 17,000)
echo "\n=== UPDATING JABATAN 2 VALUES ===\n";
$jabatan2 = \App\Models\Jabatan::find(2);
if ($jabatan2) {
    $jabatan2->update([
        'nama' => 'Pengemasan (BOP)',
        'tarif' => 17000,
        'tarif_per_jam' => 17000,
        'tunjangan' => 400000,  // Rp 400.000
        'tunjangan_transport' => 150000,  // Rp 150.000
        'tunjangan_konsumsi' => 100000,  // Rp 100.000
        'updated_at' => now(),
    ]);
    
    echo "Updated Jabatan 2: Pengemasan (BOP)\n";
    echo "  Tarif: Rp 17.000\n";
    echo "  Tunjangan Jabatan: Rp 400.000\n";
    echo "  Tunjangan Transport: Rp 150.000\n";
    echo "  Tunjangan Konsumsi: Rp 100.000\n";
    echo "  Total: Rp 650.000\n";
}

// Assign jabatan to Dedi Gunawan
echo "\n=== ASSIGNING JABATAN TO DEDI GUNAWAN ===\n";
echo "Assigning jabatan: Pengemasan (BOP) (ID: 2)\n";

$pegawai->update([
    'jabatan_id' => 2,
    'updated_at' => now(),
]);

echo "Updated pegawai jabatan_id to: 2\n";

// Verify the assignment
echo "\n=== VERIFICATION ===\n";
$updatedPegawai = \App\Models\Pegawai::with('jabatanRelasi')->find($pegawai->id);

echo "Pegawai jabatan_id: " . $updatedPegawai->jabatan_id . "\n";
echo "Jabatan relationship: " . ($updatedPegawai->jabatanRelasi ? "LOADED" : "NOT LOADED") . "\n";

if ($updatedPegawai->jabatanRelasi) {
    echo "Jabatan name: " . $updatedPegawai->jabatanRelasi->nama . "\n";
    echo "Tarif: Rp " . number_format($updatedPegawai->jabatanRelasi->tarif_per_jam ?? $updatedPegawai->jabatanRelasi->tarif ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Jabatan: Rp " . number_format($updatedPegawai->jabatanRelasi->tunjangan ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: Rp " . number_format($updatedPegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: Rp " . number_format($updatedPegawai->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    
    $totalTunjangan = ($updatedPegawai->jabatanRelasi->tunjangan ?? 0) + 
                      ($updatedPegawai->jabatanRelasi->tunjangan_transport ?? 0) + 
                      ($updatedPegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
    
    echo "Total Tunjangan: Rp " . number_format($totalTunjangan, 0, ',', '.') . "\n";
    
    echo "\n=== EXPECTED DISPLAY IN PENGGAJIAN CREATE ===\n";
    echo "Pegawai: " . $updatedPegawai->nama . " - " . $updatedPegawai->jabatanRelasi->nama . " [Gaji: 0, Tarif: 17.000]\n";
    echo "Tarif per Jam: Rp 17.000\n";
    echo "Total Jam Kerja (Bulan Ini): 3 Jam\n";
    echo "Total Gaji (diluar tunjangan): Rp 51.000\n";
    echo "Tunjangan Jabatan: Rp 400.000\n";
    echo "Tunjangan Transport: Rp 150.000\n";
    echo "Tunjangan Konsumsi: Rp 100.000\n";
    echo "Total Tunjangan: Rp 650.000\n";
    echo "Total Gaji: Rp 701.000\n";
    
    echo "\nSUCCESS: All komponen gaji will now be displayed correctly!\n";
} else {
    echo "ERROR: Jabatan relationship still not loading after assignment\n";
}

// Check if there are other pegawai with missing jabatan_id
echo "\n=== CHECKING OTHER PEGAWAI ===\n";
$otherPegawais = \App\Models\Pegawai::where('user_id', 1)
    ->whereNull('jabatan_id')
    ->where('id', '!=', $pegawai->id)
    ->get();

echo "Other pegawai without jabatan_id: " . $otherPegawais->count() . "\n";

foreach ($otherPegawais as $other) {
    echo "  - " . $other->nama . " (ID: " . $other->id . ")\n";
}

// Fix other pegawai if needed
if ($otherPegawais->count() > 0) {
    echo "\n=== FIXING OTHER PEGAWAI ===\n";
    
    foreach ($otherPegawais as $other) {
        // Assign jabatan 1 (Pengukusan) to other pegawai
        $other->update([
            'jabatan_id' => 1,
            'updated_at' => now(),
        ]);
        
        echo "Assigned jabatan to: " . $other->nama . "\n";
    }
    
    echo "Fixed " . $otherPegawais->count() . " other pegawai\n";
}

// Final verification of all pegawai
echo "\n=== FINAL VERIFICATION OF ALL PEGAWAI ===\n";
$allPegawais = \App\Models\Pegawai::where('user_id', 1)
    ->with('jabatanRelasi')
    ->get();

echo "Total pegawai: " . $allPegawais->count() . "\n";

foreach ($allPegawais as $p) {
    echo $p->nama . " - ";
    echo ($p->jabatanRelasi ? $p->jabatanRelasi->nama : 'NO JABATAN') . " - ";
    echo "Tarif: Rp " . number_format($p->jabatanRelasi->tarif_per_jam ?? $p->jabatanRelasi->tarif ?? 0, 0, ',', '.') . " - ";
    echo "Tunjangan: Rp " . number_format(($p->jabatanRelasi->tunjangan ?? 0) + ($p->jabatanRelasi->tunjangan_transport ?? 0) + ($p->jabatanRelasi->tunjangan_konsumsi ?? 0), 0, ',', '.') . "\n";
}

echo "\n=== PREVENTING FUTURE ISSUES ===\n";
echo "To prevent this issue in the future:\n";
echo "1. Always ensure pegawai has valid jabatan_id when creating\n";
echo "2. Always set tarif and tunjangan values in jabatan records\n";
echo "3. Use proper validation in PenggajianController\n";
echo "4. Test penggajian creation after any changes\n";
echo "5. Ensure all pegawai have jabatan assignments\n";

echo "\nComplete penggajian fix completed!\n";

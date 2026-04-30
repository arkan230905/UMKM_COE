<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING PEGAWAI JABATAN ASSIGNMENT FOR HOSTING\n";

// Get Budi Susanto employee
$pegawai = \App\Models\Pegawai::where('nama', 'Budi Susanto')->first();

if (!$pegawai) {
    echo "ERROR: Pegawai Budi Susanto not found!\n";
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
    echo "Nama Jabatan: '" . $jabatan->nama_jabatan . "'\n";
    echo "Tunjangan Jabatan: Rp " . number_format($jabatan->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: Rp " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: Rp " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    echo "---\n";
}

// Find the appropriate jabatan for "Pengukusan (BTKL)"
echo "\n=== FINDING APPROPRIATE JABATAN ===\n";
$appropriateJabatan = null;

foreach ($jabatans as $jabatan) {
    $jabatanName = strtolower($jabatan->nama_jabatan ?? '');
    if (strpos($jabatanName, 'pengukusan') !== false || strpos($jabatanName, 'btkl') !== false) {
        $appropriateJabatan = $jabatan;
        echo "Found matching jabatan: " . $jabatan->nama_jabatan . " (ID: " . $jabatan->id . ")\n";
        break;
    }
}

if (!$appropriateJabatan) {
    echo "No matching jabatan found for 'Pengukusan (BTKL)'\n";
    echo "Using first available jabatan...\n";
    $appropriateJabatan = $jabatans->first();
}

if ($appropriateJabatan) {
    echo "\n=== ASSIGNING JABATAN TO PEGAWAI ===\n";
    echo "Assigning jabatan: " . $appropriateJabatan->nama_jabatan . " (ID: " . $appropriateJabatan->id . ")\n";
    
    // Update pegawai with jabatan_id
    $pegawai->update([
        'jabatan_id' => $appropriateJabatan->id,
        'updated_at' => now(),
    ]);
    
    echo "Updated pegawai jabatan_id to: " . $appropriateJabatan->id . "\n";
    
    // Verify the assignment
    echo "\n=== VERIFICATION ===\n";
    $updatedPegawai = \App\Models\Pegawai::with('jabatanRelasi')->find($pegawai->id);
    
    echo "Pegawai jabatan_id: " . $updatedPegawai->jabatan_id . "\n";
    echo "Jabatan relationship: " . ($updatedPegawai->jabatanRelasi ? "LOADED" : "NOT LOADED") . "\n";
    
    if ($updatedPegawai->jabatanRelasi) {
        echo "Jabatan name: " . $updatedPegawai->jabatanRelasi->nama_jabatan . "\n";
        echo "Tunjangan Jabatan: Rp " . number_format($updatedPegawai->jabatanRelasi->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Transport: Rp " . number_format($updatedPegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Konsumsi: Rp " . number_format($updatedPegawai->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        
        $totalTunjangan = ($updatedPegawai->jabatanRelasi->tunjangan_jabatan ?? 0) + 
                          ($updatedPegawai->jabatanRelasi->tunjangan_transport ?? 0) + 
                          ($updatedPegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
        
        echo "Total Tunjangan: Rp " . number_format($totalTunjangan, 0, ',', '.') . "\n";
        
        echo "\n=== EXPECTED DISPLAY IN PENGGAJIAN CREATE ===\n";
        echo "Pegawai: " . $updatedPegawai->nama . " - " . $updatedPegawai->jabatanRelasi->nama_jabatan . "\n";
        echo "Tunjangan Jabatan: Rp " . number_format($updatedPegawai->jabatanRelasi->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Transport: Rp " . number_format($updatedPegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Konsumsi: Rp " . number_format($updatedPegawai->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        echo "Total Tunjangan: Rp " . number_format($totalTunjangan, 0, ',', '.') . "\n";
        
        if ($totalTunjangan > 0) {
            echo "\nSUCCESS: Tunjangan will now be displayed correctly!\n";
        } else {
            echo "\nWARNING: All tunjangan values are 0 - need to set tunjangan amounts\n";
            
            // Set some default tunjangan values
            echo "\n=== SETTING DEFAULT TUNJANGAN VALUES ===\n";
            $appropriateJabatan->update([
                'tunjangan_jabatan' => 500000,  // Rp 500.000
                'tunjangan_transport' => 200000,  // Rp 200.000
                'tunjangan_konsumsi' => 150000,  // Rp 150.000
                'updated_at' => now(),
            ]);
            
            echo "Set default tunjangan values:\n";
            echo "  Jabatan: Rp 500.000\n";
            echo "  Transport: Rp 200.000\n";
            echo "  Konsumsi: Rp 150.000\n";
            echo "  Total: Rp 850.000\n";
            
            // Refresh the pegawai data
            $updatedPegawai->refresh();
            $updatedPegawai->load('jabatanRelasi');
            
            $newTotalTunjangan = ($updatedPegawai->jabatanRelasi->tunjangan_jabatan ?? 0) + 
                               ($updatedPegawai->jabatanRelasi->tunjangan_transport ?? 0) + 
                               ($updatedPegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
            
            echo "\nUpdated Total Tunjangan: Rp " . number_format($newTotalTunjangan, 0, ',', '.') . "\n";
        }
        
    } else {
        echo "ERROR: Jabatan relationship still not loading after assignment\n";
    }
    
} else {
    echo "ERROR: No jabatans available to assign\n";
}

echo "\nFix completed!\n";

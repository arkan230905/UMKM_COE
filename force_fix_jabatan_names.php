<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FORCE FIXING JABATAN NAMES FOR HOSTING\n";

// Check current jabatan data directly from database
echo "\n=== CURRENT DATABASE STATE ===\n";
$jabatans = \Illuminate\Support\Facades\DB::table('jabatans')->where('user_id', 1)->get();
echo "Jabatans in database: " . $jabatans->count() . "\n";

foreach ($jabatans as $jabatan) {
    echo "ID: " . $jabatan->id . "\n";
    echo "Nama Jabatan: '" . $jabatan->nama_jabatan . "' (length: " . strlen($jabatan->nama_jabatan ?? '') . ")\n";
    echo "Tunjangan Jabatan: " . ($jabatan->tunjangan_jabatan ?? 'NULL') . "\n";
    echo "---\n";
}

// Force update using raw SQL to ensure it works
echo "\n=== FORCE UPDATING WITH RAW SQL ===\n";

// Update jabatan 1
$updated1 = \Illuminate\Support\Facades\DB::table('jabatans')
    ->where('id', 1)
    ->where('user_id', 1)
    ->update([
        'nama_jabatan' => 'Pengukusan (BTKL)',
        'tunjangan_jabatan' => 500000,
        'tunjangan_transport' => 200000,
        'tunjangan_konsumsi' => 150000,
        'updated_at' => now(),
    ]);

echo "Updated jabatan 1: " . ($updated1 ? "SUCCESS" : "FAILED") . "\n";

// Update jabatan 2
$updated2 = \Illuminate\Support\Facades\DB::table('jabatans')
    ->where('id', 2)
    ->where('user_id', 1)
    ->update([
        'nama_jabatan' => 'Pengemasan (BOP)',
        'tunjangan_jabatan' => 400000,
        'tunjangan_transport' => 150000,
        'tunjangan_konsumsi' => 100000,
        'updated_at' => now(),
    ]);

echo "Updated jabatan 2: " . ($updated2 ? "SUCCESS" : "FAILED") . "\n";

// Verify the updates
echo "\n=== VERIFICATION AFTER UPDATE ===\n";
$jabatansAfter = \Illuminate\Support\Facades\DB::table('jabatans')->where('user_id', 1)->get();

foreach ($jabatansAfter as $jabatan) {
    echo "ID: " . $jabatan->id . "\n";
    echo "Nama Jabatan: '" . $jabatan->nama_jabatan . "'\n";
    echo "Tunjangan Jabatan: " . number_format($jabatan->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Transport: " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    
    $total = ($jabatan->tunjangan_jabatan ?? 0) + ($jabatan->tunjangan_transport ?? 0) + ($jabatan->tunjangan_konsumsi ?? 0);
    echo "Total Tunjangan: " . number_format($total, 0, ',', '.') . "\n";
    echo "---\n";
}

// Test with Eloquent model
echo "\n=== TESTING WITH ELOQUENT MODEL ===\n";
$jabatan1 = \App\Models\Jabatan::find(1);
echo "Eloquent Jabatan 1: '" . ($jabatan1->nama_jabatan ?? 'NULL') . "'\n";

$jabatan2 = \App\Models\Jabatan::find(2);
echo "Eloquent Jabatan 2: '" . ($jabatan2->nama_jabatan ?? 'NULL') . "'\n";

// Test pegawai relationship
echo "\n=== TESTING PEGAWAI RELATIONSHIP ===\n";
$pegawai = \App\Models\Pegawai::where('nama', 'Budi Susanto')->with('jabatanRelasi')->first();

if ($pegawai) {
    echo "Pegawai: " . $pegawai->nama . "\n";
    echo "Jabatan ID: " . $pegawai->jabatan_id . "\n";
    
    if ($pegawai->jabatanRelasi) {
        echo "Jabatan from relationship: '" . $pegawai->jabatanRelasi->nama_jabatan . "'\n";
        echo "Tunjangan Jabatan: " . number_format($pegawai->jabatanRelasi->tunjangan_jabatan ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Transport: " . number_format($pegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Konsumsi: " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        
        $totalTunjangan = ($pegawai->jabatanRelasi->tunjangan_jabatan ?? 0) + 
                          ($pegawai->jabatanRelasi->tunjangan_transport ?? 0) + 
                          ($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
        
        echo "Total Tunjangan: " . number_format($totalTunjangan, 0, ',', '.') . "\n";
        
        echo "\n=== FINAL EXPECTED DISPLAY ===\n";
        echo "Pegawai: " . $pegawai->nama . " - " . $pegawai->jabatanRelasi->nama_jabatan . " [Gaji: 0, Tarif: 20.000]\n";
        echo "Tunjangan Jabatan: " . number_format($pegawai->jabatanRelasi->tunjangan_jabatan, 0, ',', '.') . "\n";
        echo "Tunjangan Transport: " . number_format($pegawai->jabatanRelasi->tunjangan_transport, 0, ',', '.') . "\n";
        echo "Tunjangan Konsumsi: " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi, 0, ',', '.') . "\n";
        echo "Total Tunjangan: " . number_format($totalTunjangan, 0, ',', '.') . "\n";
        
        if ($pegawai->jabatanRelasi->tunjangan_jabatan > 0) {
            echo "\nSUCCESS: Tunjangan jabatan will now be displayed correctly!\n";
            echo "The penggajian tunjangan issue is resolved for hosting.\n";
        } else {
            echo "\nISSUE: Tunjangan jabatan is still 0\n";
        }
    } else {
        echo "ERROR: Jabatan relationship not working\n";
    }
} else {
    echo "ERROR: Pegawai not found\n";
}

echo "\nForce fix completed!\n";

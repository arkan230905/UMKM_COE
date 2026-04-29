<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Pegawai-Jabatan Relation...\n\n";

// Get all pegawais with their raw data
$pegawais = \App\Models\Pegawai::withoutGlobalScopes()->get();

echo "Found {$pegawais->count()} pegawais\n\n";

foreach ($pegawais as $pegawai) {
    echo "Pegawai: {$pegawai->nama}\n";
    echo "ID: {$pegawai->id}\n";
    echo "Jabatan (text): '{$pegawai->jabatan}'\n";
    echo "Jabatan ID: " . ($pegawai->jabatan_id ?? 'NULL') . "\n";
    echo "User ID: " . ($pegawai->user_id ?? 'NULL') . "\n";
    
    // Check if jabatan_id exists in jabatan table
    if ($pegawai->jabatan_id) {
        $jabatan = \App\Models\Jabatan::withoutGlobalScopes()->find($pegawai->jabatan_id);
        if ($jabatan) {
            echo "Jabatan Found (ID {$pegawai->jabatan_id}): {$jabatan->nama}\n";
            echo "  Gaji Pokok: " . number_format($jabatan->gaji_pokok ?? 0, 0, ',', '.') . "\n";
            echo "  Tunjangan: " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.') . "\n";
            echo "  Tunjangan Transport: " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
            echo "  Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        } else {
            echo "Jabatan ID {$pegawai->jabatan_id} NOT FOUND in jabatan table!\n";
        }
    } else {
        echo "No Jabatan ID - checking by name...\n";
        
        // Try to find jabatan by name
        $jabatanByName = \App\Models\Jabatan::withoutGlobalScopes()
            ->where('nama', $pegawai->jabatan)
            ->first();
            
        if ($jabatanByName) {
            echo "Found Jabatan by Name: {$jabatanByName->nama} (ID: {$jabatanByName->id})\n";
            echo "  Suggestion: Update pegawai.jabatan_id to {$jabatanByName->id}\n";
        } else {
            echo "No Jabaton found with name '{$pegawai->jabatan}'\n";
        }
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Check all available jabatan
echo "All Available Jabatan:\n";
echo "======================\n";

$jabatans = \App\Models\Jabatan::withoutGlobalScopes()->get();
foreach ($jabatans as $jabatan) {
    echo "ID: {$jabatan->id}, Nama: {$jabatan->nama}, User ID: " . ($jabatan->user_id ?? 'NULL') . "\n";
    echo "  Gaji: " . number_format($jabatan->gaji_pokok ?? 0, 0, ',', '.') . "\n";
    echo "  Tunjangan: " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.') . "\n";
    echo "  Transport: " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
    echo "  Konsumsi: " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
    echo "\n";
}

echo "Pegawai-Jabatan relation test completed!\n";

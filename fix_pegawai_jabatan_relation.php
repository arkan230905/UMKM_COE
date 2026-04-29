<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing Pegawai-Jabatan Relations...\n\n";

try {
    // Get all pegawais with NULL jabatan_id
    $pegawais = \App\Models\Pegawai::withoutGlobalScopes()
        ->whereNull('jabatan_id')
        ->whereNotNull('jabatan')
        ->get();

    echo "Found {$pegawais->count()} pegawais with NULL jabatan_id\n\n";

    $updatedCount = 0;
    foreach ($pegawais as $pegawai) {
        echo "Processing: {$pegawai->nama} (Jabatan: {$pegawai->jabatan})\n";
        
        // Find matching jabatan by name
        $jabatan = \App\Models\Jabatan::withoutGlobalScopes()
            ->where('nama', $pegawai->jabatan)
            ->first();
            
        if ($jabatan) {
            // Update pegawai jabatan_id
            $pegawai->jabatan_id = $jabatan->id;
            $pegawai->save();
            
            echo "  Updated jabatan_id to {$jabatan->id}\n";
            echo "  Jabatan data:\n";
            echo "    Gaji Pokok: " . number_format($jabatan->gaji_pokok ?? 0, 0, ',', '.') . "\n";
            echo "    Tunjangan: " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.') . "\n";
            echo "    Transport: " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
            echo "    Konsumsi: " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
            
            $updatedCount++;
        } else {
            echo "  No matching jabatan found for '{$pegawai->jabatan}'\n";
        }
        
        echo "\n";
    }
    
    echo "Successfully updated {$updatedCount} pegawai records\n\n";
    
    // Verify the fix
    echo "Verification:\n";
    echo "============\n";
    
    $testPegawais = \App\Models\Pegawai::with('jabatanRelasi')->get();
    foreach ($testPegawais as $pegawai) {
        echo "Pegawai: {$pegawai->nama}\n";
        echo "  Jabatan ID: " . ($pegawai->jabatan_id ?? 'NULL') . "\n";
        
        $jabatan = $pegawai->jabatanRelasi;
        if ($jabatan) {
            echo "  Relation: FOUND - {$jabatan->nama}\n";
            echo "  Total Tunjangan: " . number_format(
                ($jabatan->tunjangan ?? 0) + 
                ($jabatan->tunjangan_transport ?? 0) + 
                ($jabatan->tunjangan_konsumsi ?? 0), 
                0, ',', '.'
            ) . "\n";
        } else {
            echo "  Relation: NOT FOUND\n";
        }
        echo "\n";
    }
    
    echo "Fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

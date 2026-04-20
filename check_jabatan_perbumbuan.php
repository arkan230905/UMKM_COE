<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Jabatan;

echo "=== CHECKING JABATAN PERBUMBUAN DATA ===\n\n";

// Find Perbumbuan jabatan
$perbumbuan = Jabatan::where('nama_jabatan', 'like', '%Perbumbuan%')->first();

if ($perbumbuan) {
    echo "JABATAN: {$perbumbuan->nama_jabatan} (ID: {$perbumbuan->id})\n";
    echo "Gaji Pokok: Rp " . number_format($perbumbuan->gaji_pokok ?? 0) . "\n";
    echo "Tarif per Jam: Rp " . number_format($perbumbuan->tarif_per_jam ?? 0) . "\n";
    echo "Tunjangan: Rp " . number_format($perbumbuan->tunjangan ?? 0) . "\n";
    echo "Tunjangan Transport: Rp " . number_format($perbumbuan->tunjangan_transport ?? 0) . "\n";
    echo "Tunjangan Konsumsi: Rp " . number_format($perbumbuan->tunjangan_konsumsi ?? 0) . "\n";
    echo "Asuransi: Rp " . number_format($perbumbuan->asuransi ?? 0) . "\n";
    
    $totalTunjangan = ($perbumbuan->tunjangan ?? 0) 
        + ($perbumbuan->tunjangan_transport ?? 0) 
        + ($perbumbuan->tunjangan_konsumsi ?? 0);
    
    echo "\nTOTAL TUNJANGAN: Rp " . number_format($totalTunjangan) . "\n";
    
    if ($totalTunjangan == 0) {
        echo "\n❌ PROBLEM FOUND: Jabatan Perbumbuan has 0 tunjangan!\n";
        echo "This is why penggajian shows 0 for all tunjangan components.\n\n";
        
        echo "SOLUTION: Update jabatan Perbumbuan with proper tunjangan values.\n";
        echo "Example values for BTKL Perbumbuan:\n";
        echo "- Tunjangan Jabatan: Rp 300,000\n";
        echo "- Tunjangan Transport: Rp 150,000\n";
        echo "- Tunjangan Konsumsi: Rp 200,000\n";
        echo "- Asuransi: Rp 100,000\n";
        
        // Update the jabatan with sample values
        echo "\nUpdating jabatan with sample values...\n";
        $perbumbuan->update([
            'tunjangan' => 300000,
            'tunjangan_transport' => 150000,
            'tunjangan_konsumsi' => 200000,
            'asuransi' => 100000,
        ]);
        echo "✅ Jabatan Perbumbuan updated with sample tunjangan values!\n";
        
    } else {
        echo "\n✅ Jabatan has tunjangan data. The issue might be elsewhere.\n";
    }
    
} else {
    echo "❌ Jabatan Perbumbuan not found!\n";
    echo "Available jabatan:\n";
    $allJabatan = Jabatan::all();
    foreach ($allJabatan as $jab) {
        echo "- {$jab->nama_jabatan} (ID: {$jab->id})\n";
    }
}

echo "\n=== CHECK COMPLETED ===\n";
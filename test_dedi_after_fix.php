<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING DEDI GUNAWAN AFTER FIX ===\n\n";

// Test using Eloquent model like the controller does
$pegawai = \App\Models\Pegawai::with('jabatanRelasi')->where('nama', 'like', '%Dedi%')->first();

if ($pegawai) {
    echo "PEGAWAI DATA:\n";
    echo "ID: {$pegawai->id}\n";
    echo "Nama: {$pegawai->nama}\n";
    echo "Jabatan ID: {$pegawai->jabatan_id}\n";
    echo "Jenis Pegawai: {$pegawai->jenis_pegawai}\n";
    echo "\n";
    
    if ($pegawai->jabatanRelasi) {
        echo "JABATAN RELASI DATA:\n";
        echo "Jabatan ID: {$pegawai->jabatanRelasi->id}\n";
        echo "Nama Jabatan: {$pegawai->jabatanRelasi->nama}\n";
        echo "Kategori: {$pegawai->jabatanRelasi->kategori}\n";
        echo "Gaji Pokok: " . number_format($pegawai->jabatanRelasi->gaji_pokok ?? 0, 0, ',', '.') . "\n";
        echo "Tarif per Jam: " . number_format($pegawai->jabatanRelasi->tarif_per_jam ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan: " . number_format($pegawai->jabatanRelasi->tunjangan ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Transport: " . number_format($pegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.') . "\n";
        echo "Tunjangan Konsumsi: " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.') . "\n";
        echo "Asuransi: " . number_format($pegawai->jabatanRelasi->asuransi ?? 0, 0, ',', '.') . "\n";
        
        // Calculate like the controller does
        $gajiPokok = $pegawai->jabatanRelasi->gaji_pokok ?? $pegawai->gaji_pokok ?? 0;
        $tarif = $pegawai->jabatanRelasi->tarif_per_jam ?? $pegawai->tarif_per_jam ?? 0;
        $tunjanganJabatan = $pegawai->jabatanRelasi->tunjangan ?? 0;
        $tunjanganTransport = $pegawai->jabatanRelasi->tunjangan_transport ?? 0;
        $tunjanganKonsumsi = $pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0;
        $asuransi = $pegawai->jabatanRelasi->asuransi ?? 0;
        
        $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
        
        echo "\nCALCULATED VALUES (like controller):\n";
        echo "Gaji Pokok: " . number_format($gajiPokok, 0, ',', '.') . "\n";
        echo "Tarif per Jam: " . number_format($tarif, 0, ',', '.') . "\n";
        echo "Total Tunjangan: " . number_format($totalTunjangan, 0, ',', '.') . "\n";
        echo "Asuransi: " . number_format($asuransi, 0, ',', '.') . "\n";
        
        // For BTKTL, total gaji = gaji pokok + total tunjangan + asuransi
        $totalGaji = $gajiPokok + $totalTunjangan + $asuransi;
        echo "TOTAL GAJI (BTKTL): " . number_format($totalGaji, 0, ',', '.') . "\n";
        
        echo "\nVIEW DATA ATTRIBUTES (what will show in dropdown):\n";
        echo "data-gaji-pokok: " . ($pegawai->jabatanRelasi->gaji_pokok ?? $pegawai->gaji_pokok ?? 0) . "\n";
        echo "data-tarif: " . ($pegawai->jabatanRelasi->tarif_per_jam ?? $pegawai->tarif_per_jam ?? 0) . "\n";
        echo "Display text: {$pegawai->nama} - {$pegawai->jabatan_nama} (BTKTL) [Gaji: " . number_format($pegawai->jabatanRelasi->gaji_pokok ?? $pegawai->gaji_pokok ?? 0, 0, ',', '.') . ", Tarif: " . number_format($pegawai->jabatanRelasi->tarif_per_jam ?? $pegawai->tarif_per_jam ?? 0, 0, ',', '.') . "]\n";
        
    } else {
        echo "❌ JABATAN RELASI NOT FOUND!\n";
    }
} else {
    echo "❌ DEDI GUNAWAN NOT FOUND!\n";
}

echo "\n=== TEST COMPLETE ===\n";
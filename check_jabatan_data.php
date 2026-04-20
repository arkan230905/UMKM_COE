<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pegawai;
use App\Models\Presensi;

echo "=== CHECKING JABATAN AND PRESENSI DATA ===\n\n";

// Check Ahmad Suryanto's data
$ahmad = Pegawai::with('jabatanRelasi')->where('nama', 'like', '%Ahmad%')->first();

if ($ahmad) {
    echo "PEGAWAI: {$ahmad->nama}\n";
    echo "ID: {$ahmad->id}\n";
    echo "Jenis: " . ($ahmad->jenis_pegawai ?? $ahmad->kategori ?? 'N/A') . "\n";
    echo "Jabatan ID: " . ($ahmad->jabatan_id ?? 'NULL') . "\n";
    
    if ($ahmad->jabatanRelasi) {
        echo "\nKUALIFIKASI (JABATAN) DATA:\n";
        echo "- Nama Jabatan: {$ahmad->jabatanRelasi->nama_jabatan}\n";
        echo "- Gaji Pokok: Rp " . number_format($ahmad->jabatanRelasi->gaji_pokok ?? 0) . "\n";
        echo "- Tarif per Jam: Rp " . number_format($ahmad->jabatanRelasi->tarif_per_jam ?? 0) . "\n";
        echo "- Tunjangan: Rp " . number_format($ahmad->jabatanRelasi->tunjangan ?? 0) . "\n";
        echo "- Tunjangan Transport: Rp " . number_format($ahmad->jabatanRelasi->tunjangan_transport ?? 0) . "\n";
        echo "- Tunjangan Konsumsi: Rp " . number_format($ahmad->jabatanRelasi->tunjangan_konsumsi ?? 0) . "\n";
        echo "- Asuransi: Rp " . number_format($ahmad->jabatanRelasi->asuransi ?? 0) . "\n";
    } else {
        echo "\nERROR: No jabatan relation found!\n";
        echo "Direct pegawai data:\n";
        echo "- Gaji Pokok: Rp " . number_format($ahmad->gaji_pokok ?? 0) . "\n";
        echo "- Tarif per Jam: Rp " . number_format($ahmad->tarif_per_jam ?? 0) . "\n";
    }
    
    // Check presensi data for current month
    echo "\nPRESENSI DATA (April 2026):\n";
    $presensiData = Presensi::where('pegawai_id', $ahmad->id)
        ->whereMonth('tgl_presensi', 4)
        ->whereYear('tgl_presensi', 2026)
        ->where('status', 'hadir')
        ->get();
    
    $totalJam = 0;
    $jumlahHari = 0;
    
    foreach ($presensiData as $presensi) {
        $jamKerja = $presensi->jumlah_jam;
        echo "- {$presensi->tgl_presensi}: {$presensi->jam_masuk} - {$presensi->jam_keluar} = {$jamKerja} jam\n";
        $totalJam += $jamKerja;
        $jumlahHari++;
    }
    
    echo "\nSUMMARY:\n";
    echo "- Jumlah hari hadir: {$jumlahHari}\n";
    echo "- Total jam kerja: {$totalJam}\n";
    
    if ($ahmad->jabatanRelasi && $ahmad->jabatanRelasi->tarif_per_jam > 0) {
        $gajiDasar = $ahmad->jabatanRelasi->tarif_per_jam * $totalJam;
        echo "- Gaji Dasar (Tarif × Jam): Rp " . number_format($gajiDasar) . "\n";
        
        $totalTunjangan = ($ahmad->jabatanRelasi->tunjangan ?? 0) 
            + ($ahmad->jabatanRelasi->tunjangan_transport ?? 0) 
            + ($ahmad->jabatanRelasi->tunjangan_konsumsi ?? 0);
        
        $totalGaji = $gajiDasar + $totalTunjangan + ($ahmad->jabatanRelasi->asuransi ?? 0);
        echo "- Total Tunjangan: Rp " . number_format($totalTunjangan) . "\n";
        echo "- Asuransi: Rp " . number_format($ahmad->jabatanRelasi->asuransi ?? 0) . "\n";
        echo "- TOTAL GAJI SEHARUSNYA: Rp " . number_format($totalGaji) . "\n";
    }
    
} else {
    echo "Ahmad Suryanto not found!\n";
}

echo "\n=== CHECK COMPLETED ===\n";
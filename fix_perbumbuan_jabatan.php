<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Jabatan;
use App\Models\Pegawai;

echo "=== FIXING PERBUMBUAN JABATAN DATA ===\n\n";

// Find and update Perbumbuan jabatan
$perbumbuan = Jabatan::where('nama_jabatan', 'like', '%Perbumbuan%')->first();

if ($perbumbuan) {
    echo "Found Jabatan: {$perbumbuan->nama_jabatan}\n";
    echo "Current data:\n";
    echo "- Tarif per Jam: Rp " . number_format($perbumbuan->tarif_per_jam ?? 0) . "\n";
    echo "- Tunjangan: Rp " . number_format($perbumbuan->tunjangan ?? 0) . "\n";
    echo "- Tunjangan Transport: Rp " . number_format($perbumbuan->tunjangan_transport ?? 0) . "\n";
    echo "- Tunjangan Konsumsi: Rp " . number_format($perbumbuan->tunjangan_konsumsi ?? 0) . "\n";
    echo "- Asuransi: Rp " . number_format($perbumbuan->asuransi ?? 0) . "\n";
    
    // Update with proper BTKL values
    $perbumbuan->update([
        'tarif_per_jam' => 18000,        // Rp 18,000 per hour
        'tunjangan' => 300000,           // Tunjangan jabatan Rp 300,000
        'tunjangan_transport' => 150000, // Transport Rp 150,000
        'tunjangan_konsumsi' => 200000,  // Konsumsi Rp 200,000
        'asuransi' => 100000,           // Asuransi Rp 100,000
    ]);
    
    echo "\n✅ Updated Perbumbuan jabatan with:\n";
    echo "- Tarif per Jam: Rp 18,000\n";
    echo "- Tunjangan Jabatan: Rp 300,000\n";
    echo "- Tunjangan Transport: Rp 150,000\n";
    echo "- Tunjangan Konsumsi: Rp 200,000\n";
    echo "- Asuransi: Rp 100,000\n";
    echo "- TOTAL TUNJANGAN: Rp 650,000\n";
    
} else {
    echo "❌ Jabatan Perbumbuan not found!\n";
}

// Also check and update other BTKL jabatan if needed
echo "\n=== CHECKING OTHER BTKL JABATAN ===\n";

$btklJabatan = ['Penggorengan', 'Pengemasan'];

foreach ($btklJabatan as $namaJabatan) {
    $jabatan = Jabatan::where('nama_jabatan', 'like', "%{$namaJabatan}%")->first();
    
    if ($jabatan) {
        echo "\nJabatan: {$jabatan->nama_jabatan}\n";
        $totalTunjangan = ($jabatan->tunjangan ?? 0) + ($jabatan->tunjangan_transport ?? 0) + ($jabatan->tunjangan_konsumsi ?? 0);
        
        if ($totalTunjangan == 0) {
            echo "- Updating with sample values...\n";
            $jabatan->update([
                'tarif_per_jam' => $namaJabatan == 'Penggorengan' ? 20000 : 17000,
                'tunjangan' => 250000,
                'tunjangan_transport' => 150000,
                'tunjangan_konsumsi' => 200000,
                'asuransi' => 100000,
            ]);
            echo "- ✅ Updated!\n";
        } else {
            echo "- Already has tunjangan data\n";
        }
    }
}

echo "\n=== VERIFICATION ===\n";
echo "Now test the penggajian:\n";
echo "1. Go to /transaksi/penggajian/create\n";
echo "2. Select Ahmad Suryanto\n";
echo "3. Check that tunjangan fields show proper values\n";
echo "4. Create new penggajian record\n";
echo "5. Check detail view shows correct tunjangan breakdown\n";

echo "\n=== FIX COMPLETED ===\n";
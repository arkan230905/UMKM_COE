<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pegawai;
use App\Models\Penggajian;

echo "=== CHECKING AHMAD SURYANTO DATA ===\n\n";

// Find Ahmad Suryanto
$ahmad = Pegawai::with('jabatanRelasi')->where('nama', 'like', '%Ahmad%')->first();

if ($ahmad) {
    echo "PEGAWAI: {$ahmad->nama} (ID: {$ahmad->id})\n";
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
        
        $totalTunjangan = ($ahmad->jabatanRelasi->tunjangan ?? 0) 
            + ($ahmad->jabatanRelasi->tunjangan_transport ?? 0) 
            + ($ahmad->jabatanRelasi->tunjangan_konsumsi ?? 0);
        echo "- TOTAL TUNJANGAN: Rp " . number_format($totalTunjangan) . "\n";
        
        if ($totalTunjangan == 0) {
            echo "\n❌ PROBLEM: All tunjangan values are 0 in jabatan data!\n";
            echo "This is why penggajian shows 0 for tunjangan.\n";
        } else {
            echo "\n✅ Jabatan has tunjangan data.\n";
        }
    } else {
        echo "\n❌ ERROR: No jabatan relation found!\n";
    }
    
    // Check latest penggajian record
    echo "\n=== LATEST PENGGAJIAN RECORD ===\n";
    $latestPenggajian = Penggajian::where('pegawai_id', $ahmad->id)->latest()->first();
    
    if ($latestPenggajian) {
        echo "Penggajian ID: {$latestPenggajian->id}\n";
        echo "Created: {$latestPenggajian->created_at}\n";
        echo "Tarif per Jam: Rp " . number_format($latestPenggajian->tarif_per_jam ?? 0) . "\n";
        echo "Total Jam Kerja: " . ($latestPenggajian->total_jam_kerja ?? 0) . " jam\n";
        echo "Tunjangan (legacy): Rp " . number_format($latestPenggajian->tunjangan ?? 0) . "\n";
        echo "Tunjangan Jabatan: Rp " . number_format($latestPenggajian->tunjangan_jabatan ?? 0) . "\n";
        echo "Tunjangan Transport: Rp " . number_format($latestPenggajian->tunjangan_transport ?? 0) . "\n";
        echo "Tunjangan Konsumsi: Rp " . number_format($latestPenggajian->tunjangan_konsumsi ?? 0) . "\n";
        echo "Total Tunjangan: Rp " . number_format($latestPenggajian->total_tunjangan ?? 0) . "\n";
        echo "Asuransi: Rp " . number_format($latestPenggajian->asuransi ?? 0) . "\n";
        echo "Total Gaji: Rp " . number_format($latestPenggajian->total_gaji ?? 0) . "\n";
        
        // Check if this was created with old or new method
        if ($latestPenggajian->created_at > '2026-04-20 09:00:00') {
            echo "\n📅 This record was created AFTER the fix (should have correct data)\n";
        } else {
            echo "\n📅 This record was created BEFORE the fix (may have old data)\n";
        }
    } else {
        echo "No penggajian records found for Ahmad.\n";
    }
    
} else {
    echo "Ahmad Suryanto not found!\n";
}

echo "\n=== SOLUTION ===\n";
echo "If jabatan has 0 tunjangan values:\n";
echo "1. Go to Master Data > Jabatan\n";
echo "2. Edit 'Perbumbuan' jabatan\n";
echo "3. Set proper tunjangan values\n";
echo "4. Create new penggajian record\n";
echo "\nIf jabatan has correct values but penggajian shows 0:\n";
echo "1. This might be an old record\n";
echo "2. Create a new penggajian to test the fix\n";

echo "\n=== CHECK COMPLETED ===\n";
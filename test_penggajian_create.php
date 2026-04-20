<?php
/**
 * Simple test to create penggajian record
 */

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Penggajian;
use App\Models\Pegawai;
use App\Models\Coa;

try {
    echo "Testing penggajian creation...\n";
    
    // Get first pegawai
    $pegawai = Pegawai::first();
    if (!$pegawai) {
        echo "❌ No pegawai found\n";
        exit(1);
    }
    
    // Get kas/bank account
    $kasBank = Coa::where('kode_akun', '112')->first();
    if (!$kasBank) {
        echo "❌ No kas/bank account found\n";
        exit(1);
    }
    
    echo "Creating penggajian for: {$pegawai->nama}\n";
    
    // Create simple penggajian record
    $penggajian = new Penggajian([
        'pegawai_id' => $pegawai->id,
        'tanggal_penggajian' => now(),
        'coa_kasbank' => $kasBank->kode_akun,
        'gaji_pokok' => 2500000,
        'tarif_per_jam' => 20000,
        'tunjangan' => 525000,
        'asuransi' => 100000,
        'bonus' => 0,
        'potongan' => 0,
        'total_jam_kerja' => 7,
        'total_gaji' => 765000,
        'status_pembayaran' => 'belum_lunas',
    ]);
    
    if ($penggajian->save()) {
        echo "✅ Penggajian created successfully with ID: {$penggajian->id}\n";
        echo "Total gaji: Rp " . number_format($penggajian->total_gaji, 0, ',', '.') . "\n";
    } else {
        echo "❌ Failed to save penggajian\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
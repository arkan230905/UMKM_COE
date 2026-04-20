<?php
/**
 * Script sederhana untuk update nilai buku aset
 * Jalankan script ini untuk memperbarui semua nilai buku aset sesuai bulan saat ini
 */

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aset;
use Carbon\Carbon;

try {
    echo "=== UPDATE NILAI BUKU ASET ===\n";
    echo "Tanggal: " . Carbon::now()->format('d/m/Y H:i:s') . "\n\n";
    
    // Ambil semua aset aktif
    $asets = Aset::where('status', 'aktif')
        ->whereNotNull('umur_manfaat')
        ->where('umur_manfaat', '>', 0)
        ->get();
    
    echo "Ditemukan {$asets->count()} aset untuk diupdate\n\n";
    
    $updated = 0;
    
    foreach ($asets as $aset) {
        echo "Processing: {$aset->nama_aset}\n";
        
        // Simpan nilai lama
        $oldNilaiBuku = $aset->nilai_buku;
        $oldAkumulasi = $aset->akumulasi_penyusutan;
        
        // Update nilai buku real-time
        $aset->updateNilaiBukuRealTime();
        
        // Refresh untuk mendapatkan nilai terbaru
        $aset->refresh();
        
        // Tampilkan perubahan
        echo "  Nilai Buku: Rp " . number_format($oldNilaiBuku, 0, ',', '.') . 
             " → Rp " . number_format($aset->nilai_buku, 0, ',', '.') . "\n";
        echo "  Akumulasi: Rp " . number_format($oldAkumulasi, 0, ',', '.') . 
             " → Rp " . number_format($aset->akumulasi_penyusutan, 0, ',', '.') . "\n";
        
        $updated++;
        echo "  ✅ Updated\n\n";
    }
    
    echo "=== SELESAI ===\n";
    echo "Total aset yang diupdate: {$updated}\n";
    echo "Semua nilai buku aset telah disesuaikan dengan bulan saat ini.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
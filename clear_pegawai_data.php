<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Membersihkan semua data pegawai...\n\n";

try {
    // Check current pegawai data
    $pegawaiCount = \DB::table('pegawais')->count();
    echo "Jumlah pegawai saat ini: $pegawaiCount\n\n";
    
    if ($pegawaiCount > 0) {
        echo "Menghapus semua data pegawai...\n";
        
        // Show data before deletion
        $pegawaiData = \DB::table('pegawais')->get();
        echo "Data pegawai yang akan dihapus:\n";
        echo str_repeat("=", 80) . "\n";
        
        foreach ($pegawaiData as $pegawai) {
            echo sprintf("ID: %d | Nama: %-20s | Email: %-30s | Jabatan: %s\n", 
                $pegawai->id, 
                $pegawai->nama_pegawai ?? 'N/A', 
                $pegawai->email ?? 'N/A', 
                $pegawai->jabatan ?? 'N/A'
            );
        }
        
        echo str_repeat("=", 80) . "\n\n";
        
        // Delete all pegawai data
        $deleted = \DB::table('pegawais')->delete();
        
        if ($deleted > 0) {
            echo "✅ Berhasil menghapus $deleted data pegawai\n";
        } else {
            echo "❌ Gagal menghapus data pegawai\n";
        }
    } else {
        echo "ℹ️  Tabel pegawai sudah kosong\n";
    }
    
    // Verify deletion
    $finalCount = \DB::table('pegawais')->count();
    echo "\nVerifikasi penghapusan:\n";
    echo "Jumlah pegawai setelah penghapusan: $finalCount\n";
    
    if ($finalCount === 0) {
        echo "✅ Tabel pegawai benar-benar kosong\n";
    } else {
        echo "⚠️  Masih ada $finalCount data pegawai\n";
    }
    
    // Reset auto-increment
    echo "\nMereset auto-increment ID pegawai...\n";
    \DB::statement('ALTER TABLE pegawais AUTO_INCREMENT = 1');
    echo "✅ Auto-increment ID pegawai direset ke 1\n";
    
    echo "\n=== PEMBERSIHAN PEGAWAI SELESAI ===\n";
    echo "Status: " . ($finalCount === 0 ? '✅ BERHASIL' : '⚠️  SEBAGIAN') . "\n";
    echo "Total data dihapus: $deleted\n";
    echo "Tabel pegawai: " . ($finalCount === 0 ? 'BERSIH' : 'MASIH ADA DATA') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error membersihkan data pegawai: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

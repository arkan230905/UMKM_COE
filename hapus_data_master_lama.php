<?php
/**
 * HAPUS DATA MASTER LAMA (user_id = NULL)
 * ========================================
 * Script ini akan menghapus data COA dan Satuan master (user_id = NULL)
 * karena sekarang setiap user sudah punya data sendiri
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Coa;
use App\Models\Satuan;

echo "==============================================\n";
echo "HAPUS DATA MASTER LAMA\n";
echo "==============================================\n\n";

// Hitung data master
$masterCoas = Coa::whereNull('user_id')->count();
$masterSatuans = Satuan::whereNull('user_id')->count();

echo "Data Master yang akan dihapus:\n";
echo "- COA: {$masterCoas}\n";
echo "- Satuan: {$masterSatuans}\n\n";

if ($masterCoas == 0 && $masterSatuans == 0) {
    echo "✅ Tidak ada data master yang perlu dihapus!\n\n";
    exit(0);
}

echo "⚠️  PERINGATAN:\n";
echo "Script ini akan menghapus SEMUA data COA dan Satuan\n";
echo "yang memiliki user_id = NULL (data master).\n\n";
echo "Pastikan Anda sudah menjalankan:\n";
echo "php copy_master_data_ke_user_existing.php\n\n";

echo "Apakah Anda yakin ingin melanjutkan? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$confirmation = trim(strtolower($line));
fclose($handle);

if ($confirmation !== 'yes') {
    echo "\n❌ Dibatalkan oleh user.\n\n";
    exit(0);
}

echo "\n";
echo "==============================================\n";
echo "MENGHAPUS DATA MASTER...\n";
echo "==============================================\n\n";

// Hapus COA master
echo "Menghapus COA master...\n";
$deletedCoas = Coa::whereNull('user_id')->delete();
echo "  ✅ {$deletedCoas} COA dihapus\n\n";

// Hapus Satuan master
echo "Menghapus Satuan master...\n";
$deletedSatuans = Satuan::whereNull('user_id')->delete();
echo "  ✅ {$deletedSatuans} Satuan dihapus\n\n";

echo "==============================================\n";
echo "RINGKASAN\n";
echo "==============================================\n\n";
echo "Total COA dihapus: {$deletedCoas}\n";
echo "Total Satuan dihapus: {$deletedSatuans}\n\n";

echo "🎉 BERHASIL!\n\n";
echo "Data master lama sudah dihapus.\n";
echo "Sekarang hanya ada data per-user.\n\n";

echo "==============================================\n";
echo "VERIFIKASI\n";
echo "==============================================\n\n";

// Verifikasi
$remainingMasterCoas = Coa::whereNull('user_id')->count();
$remainingMasterSatuans = Satuan::whereNull('user_id')->count();

if ($remainingMasterCoas == 0 && $remainingMasterSatuans == 0) {
    echo "✅ Verifikasi: Tidak ada data master tersisa\n\n";
} else {
    echo "⚠️  Masih ada data master tersisa:\n";
    echo "   - COA: {$remainingMasterCoas}\n";
    echo "   - Satuan: {$remainingMasterSatuans}\n\n";
}

echo "==============================================\n";
echo "SELESAI\n";
echo "==============================================\n";

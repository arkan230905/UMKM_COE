<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Coa;
use App\Models\Satuan;
use Database\Seeders\DefaultCoaSeederBaru;
use Database\Seeders\DefaultSatuanSeeder;

echo "==============================================\n";
echo "UPDATE COA DAN SATUAN LENGKAP\n";
echo "==============================================\n\n";

echo "PERINGATAN:\n";
echo "Script ini akan:\n";
echo "1. Hapus semua COA (user & master)\n";
echo "2. Hapus semua Satuan (user & master)\n";
echo "3. Buat COA baru (50 akun Jasuke)\n";
echo "4. Buat Satuan baru (16 satuan)\n\n";

echo "Apakah Anda yakin? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$confirmation = trim(strtolower($line));
fclose($handle);

if ($confirmation !== 'yes') {
    echo "\nDibatalkan.\n\n";
    exit(0);
}

echo "\n";
echo "==============================================\n";
echo "STEP 1: HAPUS DATA LAMA\n";
echo "==============================================\n\n";

// Hapus semua COA
echo "Menghapus semua COA...\n";
$deletedCoa = Coa::query()->delete();
echo "  {$deletedCoa} COA dihapus\n\n";

// Hapus semua Satuan
echo "Menghapus semua Satuan...\n";
$deletedSatuan = Satuan::query()->delete();
echo "  {$deletedSatuan} Satuan dihapus\n\n";

echo "==============================================\n";
echo "STEP 2: BUAT DATA BARU\n";
echo "==============================================\n\n";

// Buat data baru untuk setiap user
$users = User::all();
echo "Membuat data untuk " . $users->count() . " user...\n\n";

$coaSeeder = new DefaultCoaSeederBaru();
$satuanSeeder = new DefaultSatuanSeeder();

foreach ($users as $user) {
    echo "==============================================\n";
    echo "User: {$user->name} (ID: {$user->id})\n";
    echo "Email: {$user->email}\n";
    echo "==============================================\n\n";
    
    // Buat COA
    echo "Membuat COA...\n";
    $coaSeeder->run($user->id);
    $coaCount = Coa::where('user_id', $user->id)->count();
    echo "  ✅ {$coaCount} COA dibuat\n\n";
    
    // Buat Satuan
    echo "Membuat Satuan...\n";
    $satuanSeeder->run($user->id);
    $satuanCount = Satuan::where('user_id', $user->id)->count();
    echo "  ✅ {$satuanCount} Satuan dibuat\n\n";
}

echo "==============================================\n";
echo "RINGKASAN\n";
echo "==============================================\n\n";
echo "Total user diproses: " . $users->count() . "\n";
echo "COA per user: 50 akun\n";
echo "Satuan per user: 16 satuan\n\n";

echo "==============================================\n";
echo "DATA COA (50 AKUN):\n";
echo "==============================================\n";
echo "Aset (23): 11, 111-113, 114-1141, 115-1153, 116-1161, 117-1173, 118-127\n";
echo "Kewajiban (4): 21, 210-212\n";
echo "Modal (3): 31, 310-311\n";
echo "Pendapatan (3): 41, 410, 42\n";
echo "Biaya (17): 51, 510, 513-516, 52, 520, 53, 530-532, 54, 55, 550-553\n\n";

echo "==============================================\n";
echo "DATA SATUAN (16 SATUAN):\n";
echo "==============================================\n";
echo "1. ONS - Ons\n";
echo "2. KG - Kilogram\n";
echo "3. ML - Mililiter\n";
echo "4. G - Gram\n";
echo "5. LTR - Liter\n";
echo "6. PTG - Potong\n";
echo "7. EKOR - Ekor\n";
echo "8. SDT - Sendok Teh\n";
echo "9. SDM - Sendok Makan\n";
echo "10. PCS - Pieces\n";
echo "11. BNGKS - Bungkus\n";
echo "12. CUP - Cup\n";
echo "13. GL - Galon\n";
echo "14. TBG - Tabung\n";
echo "15. SNG - Siung\n";
echo "16. KLG - Kaleng\n\n";

echo "==============================================\n";
echo "SELESAI!\n";
echo "==============================================\n\n";
echo "🎉 BERHASIL!\n\n";
echo "Sekarang:\n";
echo "✅ Setiap user punya 50 COA (Jasuke)\n";
echo "✅ Setiap user punya 16 Satuan (lengkap)\n";
echo "✅ Tidak ada 'Tidak dapat diubah'\n";
echo "✅ Owner bisa ubah/hapus/tambah data\n\n";

echo "LANGKAH SELANJUTNYA:\n";
echo "1. Test di browser:\n";
echo "   - /master-data/coa\n";
echo "   - /master-data/satuan-dashboard\n";
echo "2. Export database\n";
echo "3. Deploy ke hosting\n\n";

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Satuan;
use Database\Seeders\DefaultSatuanSeeder;

echo "==============================================\n";
echo "UPDATE SATUAN USER LENGKAP\n";
echo "==============================================\n\n";

echo "PERINGATAN:\n";
echo "Script ini akan:\n";
echo "1. Hapus semua Satuan user yang ada\n";
echo "2. Hapus semua Satuan master (user_id = NULL)\n";
echo "3. Buat Satuan baru lengkap (16 satuan)\n\n";

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

// Hapus semua Satuan
echo "Menghapus semua Satuan...\n";
$deletedCount = Satuan::query()->delete();
echo "  {$deletedCount} Satuan dihapus\n\n";

// Buat Satuan baru untuk setiap user
$users = User::all();
echo "Membuat Satuan baru untuk " . $users->count() . " user...\n\n";

$seeder = new DefaultSatuanSeeder();

foreach ($users as $user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    $seeder->run($user->id);
    
    $satuanCount = Satuan::where('user_id', $user->id)->count();
    echo "  {$satuanCount} Satuan dibuat\n\n";
}

echo "==============================================\n";
echo "SELESAI\n";
echo "==============================================\n\n";
echo "Sekarang setiap user punya 16 Satuan lengkap:\n";
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
echo "Test di browser!\n\n";

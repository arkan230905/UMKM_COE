<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Coa;
use Database\Seeders\DefaultCoaSeederBaru;

echo "==============================================\n";
echo "UPDATE COA USER KE DATA JASUKE\n";
echo "==============================================\n\n";

echo "PERINGATAN:\n";
echo "Script ini akan:\n";
echo "1. Hapus semua COA user yang ada\n";
echo "2. Hapus semua COA master (user_id = NULL)\n";
echo "3. Buat COA baru sesuai data Jasuke\n\n";

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

// Hapus semua COA
echo "Menghapus semua COA...\n";
$deletedCount = Coa::query()->delete();
echo "  {$deletedCount} COA dihapus\n\n";

// Buat COA baru untuk setiap user
$users = User::all();
echo "Membuat COA baru untuk " . $users->count() . " user...\n\n";

$seeder = new DefaultCoaSeederBaru();

foreach ($users as $user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    $seeder->run($user->id);
    
    $coaCount = Coa::where('user_id', $user->id)->count();
    echo "  {$coaCount} COA dibuat\n\n";
}

echo "==============================================\n";
echo "SELESAI\n";
echo "==============================================\n\n";
echo "Sekarang setiap user punya 50 COA sesuai data Jasuke.\n";
echo "Test di browser!\n\n";

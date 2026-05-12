<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Coa;
use Database\Seeders\CoaTemplateSeeder;

// Ambil user terakhir yang registrasi
$user = User::latest()->first();

if (!$user || !$user->perusahaan_id) {
    echo "❌ User tidak ditemukan atau tidak punya company_id\n";
    exit(1);
}

echo "User: {$user->name}\n";
echo "Company ID: {$user->perusahaan_id}\n";

// Cek apakah COA sudah ada untuk company ini
$existingCoa = Coa::where('company_id', $user->perusahaan_id)->count();
echo "COA yang sudah ada: {$existingCoa}\n";

if ($existingCoa > 0) {
    echo "⚠️  COA sudah ada untuk company ini. Hapus dulu? (y/n): ";
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    if(trim($line) != 'y'){
        echo "Dibatalkan.\n";
        exit(0);
    }
    Coa::where('company_id', $user->perusahaan_id)->delete();
    echo "✓ COA lama dihapus\n";
}

// Copy COA template
echo "Copying COA template...\n";
CoaTemplateSeeder::copyCoaTemplateForCompany($user->perusahaan_id);

// Verifikasi
$newCoa = Coa::where('company_id', $user->perusahaan_id)->count();
echo "✓ COA berhasil di-copy! Total: {$newCoa} akun\n";

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

// Ambil user yang login
$user = \App\Models\User::latest()->first();
echo "User: {$user->name}\n";
echo "Company ID: {$user->perusahaan_id}\n\n";

// Cek COA untuk company ini dengan detail
$coas = Coa::where('company_id', $user->perusahaan_id)
    ->orderBy('kode_akun')
    ->get(['id', 'kode_akun', 'nama_akun', 'company_id', 'created_at']);

echo "Total COA: " . $coas->count() . "\n\n";

// Cek apakah ada kode_akun yang sama
$grouped = $coas->groupBy('kode_akun');
$duplicates = $grouped->filter(function($group) {
    return $group->count() > 1;
});

if ($duplicates->count() > 0) {
    echo "❌ Ditemukan kode_akun yang duplikat:\n\n";
    foreach ($duplicates as $kode => $items) {
        echo "Kode: {$kode} (Jumlah: {$items->count()})\n";
        foreach ($items as $item) {
            echo "  - ID: {$item->id}, Nama: {$item->nama_akun}, Created: {$item->created_at}\n";
        }
        echo "\n";
    }
    
    echo "\n⚠️  Hapus duplikat (keep yang pertama)? (y/n): ";
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    
    if(trim($line) == 'y'){
        foreach ($duplicates as $kode => $items) {
            $first = true;
            foreach ($items as $item) {
                if ($first) {
                    echo "  Keeping ID: {$item->id} - {$item->nama_akun}\n";
                    $first = false;
                    continue;
                }
                echo "  Deleting ID: {$item->id} - {$item->nama_akun}\n";
                Coa::withoutGlobalScopes()->find($item->id)->delete();
            }
        }
        echo "\n✓ Duplikat berhasil dihapus!\n";
        
        // Verifikasi
        $remaining = Coa::where('company_id', $user->perusahaan_id)->count();
        echo "✓ Sisa COA: {$remaining} akun\n";
    }
} else {
    echo "✓ Tidak ada duplikat\n";
}

// Tampilkan sample 10 COA pertama
echo "\nSample 10 COA pertama:\n";
$sample = Coa::where('company_id', $user->perusahaan_id)
    ->orderBy('kode_akun')
    ->limit(10)
    ->get(['id', 'kode_akun', 'nama_akun']);

foreach ($sample as $coa) {
    echo "  {$coa->id}. {$coa->kode_akun} - {$coa->nama_akun}\n";
}

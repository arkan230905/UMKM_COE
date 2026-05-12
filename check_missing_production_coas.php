<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING MISSING PRODUCTION COAs ===\n\n";

$requiredCoas = [
    '1171' => 'Pers. Barang Dalam Proses - BBB',
    '1172' => 'Pers. Barang Dalam Proses - BTKL',
    '1173' => 'Pers. Barang Dalam Proses - BOP',
    '211' => 'Hutang Gaji',
    '550' => 'BOP - Listrik',
    // Add more BOP accounts as needed
];

$missingCoas = [];

foreach ($requiredCoas as $kode => $nama) {
    $exists = DB::table('coas')
        ->where('kode_akun', $kode)
        ->where('user_id', 1)
        ->exists();
    
    if ($exists) {
        echo "✅ {$kode} - {$nama} EXISTS\n";
    } else {
        echo "❌ {$kode} - {$nama} MISSING\n";
        $missingCoas[$kode] = $nama;
    }
}

if (!empty($missingCoas)) {
    echo "\n=== MISSING COAs ===\n";
    foreach ($missingCoas as $kode => $nama) {
        echo "- {$kode}: {$nama}\n";
    }
    
    echo "\nThese COAs need to be created before production journal entries can work correctly.\n";
} else {
    echo "\n✅ All required COAs exist!\n";
}

// Check what COAs are actually being used in production journal entries
echo "\n=== COAs USED IN PRODUCTION JOURNAL ENTRIES ===\n";
$usedCoas = DB::table('jurnal_umum as ju')
    ->join('coas', 'ju.coa_id', '=', 'coas.id')
    ->where('ju.user_id', 1)
    ->whereIn('ju.tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
    ->select('coas.kode_akun', 'coas.nama_akun', 'ju.tipe_referensi')
    ->distinct()
    ->orderBy('coas.kode_akun')
    ->get();

foreach ($usedCoas as $coa) {
    echo "{$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_referensi})\n";
}

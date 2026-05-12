<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== SETUP COA UNTUK PENGGAJIAN ===\n\n";

// COA yang diperlukan untuk penggajian
$requiredCoas = [
    '52' => 'BIAYA TENAGA KERJA LANGSUNG (BTKL)',
    '54' => 'BIAYA TENAGA KERJA TIDAK LANGSUNG (BOP)',
    '513' => 'BEBAN TUNJANGAN',
    '514' => 'BEBAN ASURANSI',
    '515' => 'BEBAN BONUS',
    '516' => 'POTONGAN GAJI',
    '111' => 'KAS BANK',
    '112' => 'KAS TUNAI'
];

echo "COA yang diperlukan:\n";
foreach ($requiredCoas as $kode => $nama) {
    echo "- $kode: $nama\n";
}

echo "\nMemeriksa COA yang ada...\n";

// Periksa COA yang sudah ada
$existingCoas = DB::table('coas')
    ->select('kode_akun', 'nama_akun')
    ->where('user_id', 1) // Asumsi user ID 1 untuk setup awal
    ->get();

echo "COA yang sudah ada:\n";
foreach ($existingCoas as $coa) {
    echo "- {$coa->kode_akun}: {$coa->nama_akun}\n";
}

// Tambahkan COA yang belum ada
foreach ($requiredCoas as $kode => $nama) {
    $exists = $existingCoas->firstWhere('kode_akun', $kode);
    
    if (!$exists) {
        echo "\nMenambahkan COA $kode: $nama\n";
        
        DB::table('coas')->insert([
            'kode_akun' => $kode,
            'nama_akun' => $nama,
            'tipe_akun' => getTipeAkun($kode),
            'saldo_awal' => 0,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

function getTipeAkun($kode) {
    switch ($kode) {
        case '52':
        case '54':
        case '513':
        case '514':
        case '515':
        case '516':
            return 'Expense';
        case '111':
        case '112':
            return 'Asset';
        default:
            return 'Expense';
    }
}

echo "\n=== SELESAI ===\n";

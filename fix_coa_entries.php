<?php

// Fix missing COA entries for retur journal
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coa;

echo "Checking and Creating Missing COA Entries\n";
echo "========================================\n\n";

// Required COA codes for retur journal
$requiredCoas = [
    '2101' => [
        'nama_akun' => 'Hutang Usaha',
        'kategori_akun' => 'Kewajiban Lancar',
        'tipe_akun' => 'Kewajiban',
        'saldo_normal' => 'kredit',
        'keterangan' => 'Hutang kepada vendor/supplier',
        'saldo_awal' => 0
    ]
];

foreach ($requiredCoas as $code => $data) {
    echo "Checking COA code: {$code}\n";
    
    $existingCoa = Coa::where('kode_akun', $code)->first();
    
    if ($existingCoa) {
        echo "✅ COA {$code} already exists: {$existingCoa->nama_akun}\n";
    } else {
        try {
            $coa = Coa::create([
                'kode_akun' => $code,
                'nama_akun' => $data['nama_akun'],
                'kategori_akun' => $data['kategori_akun'],
                'tipe_akun' => $data['tipe_akun'],
                'saldo_normal' => $data['saldo_normal'],
                'keterangan' => $data['keterangan'],
                'saldo_awal' => $data['saldo_awal']
            ]);
            
            echo "✅ Created COA {$code}: {$data['nama_akun']}\n";
        } catch (Exception $e) {
            echo "❌ Error creating COA {$code}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

echo "COA setup completed!\n";
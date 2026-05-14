<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$userId = 2;

echo "=== Cek COA Persediaan untuk User ID: {$userId} ===\n\n";

$coas = \App\Models\Coa::withoutGlobalScopes()
    ->where('user_id', $userId)
    ->where('tipe_akun', 'Aset')
    ->where('kategori_akun', 'Persediaan')
    ->orderBy('kode_akun')
    ->get();

echo "Total COA Persediaan: " . $coas->count() . "\n\n";

if ($coas->count() > 0) {
    foreach($coas as $coa) {
        echo "- {$coa->kode_akun} - {$coa->nama_akun}\n";
    }
} else {
    echo "❌ Tidak ada COA Persediaan untuk user ini!\n";
    echo "Membuat COA Persediaan standar...\n\n";
    
    // Create standard COA Persediaan
    $coaData = [
        ['kode_akun' => '116', 'nama_akun' => 'Persediaan Barang Jadi', 'saldo_normal' => 'Debit'],
        ['kode_akun' => '114', 'nama_akun' => 'Persediaan Bahan Baku', 'saldo_normal' => 'Debit'],
        ['kode_akun' => '115', 'nama_akun' => 'Persediaan Bahan Pendukung', 'saldo_normal' => 'Debit'],
    ];
    
    foreach ($coaData as $data) {
        $existing = \App\Models\Coa::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('kode_akun', $data['kode_akun'])
            ->first();
        
        if (!$existing) {
            \App\Models\Coa::create([
                'user_id' => $userId,
                'company_id' => 1,
                'kode_akun' => $data['kode_akun'],
                'nama_akun' => $data['nama_akun'],
                'tipe_akun' => 'Aset',
                'kategori_akun' => 'Persediaan',
                'saldo_awal' => 0,
                'saldo_normal' => $data['saldo_normal'],
            ]);
            echo "✓ Dibuat: {$data['kode_akun']} - {$data['nama_akun']}\n";
        } else {
            echo "⚠ Sudah ada: {$data['kode_akun']} - {$data['nama_akun']}\n";
        }
    }
    
    echo "\n✓ Selesai!\n";
}

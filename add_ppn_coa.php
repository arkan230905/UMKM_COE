<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Adding PPN COA accounts...\n\n";

// Check if PPN Masukkan already exists
$existingPpnMasukan = \App\Models\Coa::where('kode_akun', '127')->first();
if ($existingPpnMasukan) {
    echo "⚠️  PPN Masukkan (127) already exists, updating...\n";
    $existingPpnMasukan->nama_akun = 'PPN Masukkan';
    $existingPpnMasukan->tipe_akun = 'Aset';
    $existingPpnMasukan->saldo_normal = 'debit';
    $existingPpnMasukan->save();
} else {
    echo "➕ Adding PPN Masukkan (127)...\n";
    $ppnMasukan = new \App\Models\Coa();
    $ppnMasukan->kode_akun = '127';
    $ppnMasukan->nama_akun = 'PPN Masukkan';
    $ppnMasukan->tipe_akun = 'Aset';
    $ppnMasukan->kategori_akun = 'PPN';
    $ppnMasukan->saldo_normal = 'debit';
    $ppnMasukan->saldo_awal = 0;
    $ppnMasukan->user_id = 1;
    $ppnMasukan->save();
    echo "✅ PPN Masukkan (127) added successfully\n";
}

// Check if PPN Keluaran already exists
$existingPpnKeluaran = \App\Models\Coa::where('kode_akun', '212')->first();
if ($existingPpnKeluaran) {
    echo "⚠️  PPN Keluaran (212) already exists, updating...\n";
    $existingPpnKeluaran->nama_akun = 'PPN Keluaran';
    $existingPpnKeluaran->tipe_akun = 'Kewajiban';
    $existingPpnKeluaran->saldo_normal = 'credit';
    $existingPpnKeluaran->save();
} else {
    echo "➕ Adding PPN Keluaran (212)...\n";
    $ppnKeluaran = new \App\Models\Coa();
    $ppnKeluaran->kode_akun = '212';
    $ppnKeluaran->nama_akun = 'PPN Keluaran';
    $ppnKeluaran->tipe_akun = 'Kewajiban';
    $ppnKeluaran->kategori_akun = 'PPN';
    $ppnKeluaran->saldo_normal = 'kredit';
    $ppnKeluaran->saldo_awal = 0;
    $ppnKeluaran->user_id = 1;
    $ppnKeluaran->save();
    echo "✅ PPN Keluaran (212) added successfully\n";
}

echo "\n📋 Verification:\n";
$ppnMasukan = \App\Models\Coa::where('kode_akun', '127')->first();
$ppnKeluaran = \App\Models\Coa::where('kode_akun', '212')->first();

if ($ppnMasukan) {
    echo "✅ PPN Masukkan: {$ppnMasukan->kode_akun} - {$ppnMasukan->nama_akun} ({$ppnMasukan->tipe_akun})\n";
} else {
    echo "❌ PPN Masukkan not found\n";
}

if ($ppnKeluaran) {
    echo "✅ PPN Keluaran: {$ppnKeluaran->kode_akun} - {$ppnKeluaran->nama_akun} ({$ppnKeluaran->tipe_akun})\n";
} else {
    echo "❌ PPN Keluaran not found\n";
}

echo "\n🎯 COA setup completed!\n";

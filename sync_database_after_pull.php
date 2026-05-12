<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK KONDISI DATABASE SETELAH PULL ===\n\n";

// 1. Cek saldo awal COA
echo "1. CEK SALDO AWAL COA:\n\n";

$coaChecks = [
    ['kode' => '1131', 'nama' => 'Persediaan Bahan Baku', 'seharusnya' => 7098715],
    ['kode' => '113', 'nama' => 'Persediaan', 'seharusnya' => 107900000],
    ['kode' => '12', 'nama' => 'Aset Tetap', 'seharusnya' => 255000000],
];

$needUpdate = false;

foreach ($coaChecks as $check) {
    $coa = \App\Models\Coa::where('kode_akun', $check['kode'])
        ->where('nama_akun', 'LIKE', "%{$check['nama']}%")
        ->first();
    
    if ($coa) {
        $saldoSekarang = (float) $coa->saldo_awal;
        $match = abs($saldoSekarang - $check['seharusnya']) < 1;
        
        echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
        echo "    Saldo Sekarang: Rp " . number_format($saldoSekarang, 0, ',', '.') . "\n";
        echo "    Seharusnya: Rp " . number_format($check['seharusnya'], 0, ',', '.') . "\n";
        echo "    Status: " . ($match ? "✅ OK" : "❌ BEDA") . "\n\n";
        
        if (!$match) {
            $needUpdate = true;
        }
    } else {
        echo "  ❌ COA {$check['kode']} - {$check['nama']} tidak ditemukan\n\n";
    }
}

// 2. Cek jurnal penyusutan April 2026
echo "2. CEK JURNAL PENYUSUTAN APRIL 2026:\n\n";

$entries = \App\Models\JournalEntry::where('ref_type', 'depreciation')
    ->whereYear('tanggal', 2026)
    ->whereMonth('tanggal', 4)
    ->with('lines.coa')
    ->get();

echo "Total entries: " . $entries->count() . "\n\n";

$expectedEntries = [
    ['aset_id' => 2, 'nama' => 'Peralatan Produksi', 'nilai' => 530400],
    ['aset_id' => 4, 'nama' => 'Mesin produksi', 'nilai' => 1333333],
    ['aset_id' => 5, 'nama' => 'Kendaraan', 'nilai' => 592593],
];

foreach ($expectedEntries as $expected) {
    $entry = $entries->where('ref_id', $expected['aset_id'])->first();
    
    if ($entry) {
        $debit = $entry->lines->sum('debit');
        $kredit = $entry->lines->sum('credit');
        $match = abs($debit - $expected['nilai']) < 1;
        
        echo "  {$expected['nama']} (ID: {$expected['aset_id']})\n";
        echo "    Debit: Rp " . number_format($debit, 0, ',', '.') . "\n";
        echo "    Kredit: Rp " . number_format($kredit, 0, ',', '.') . "\n";
        echo "    Seharusnya: Rp " . number_format($expected['nilai'], 0, ',', '.') . "\n";
        echo "    Status: " . ($match ? "✅ OK" : "❌ BEDA") . "\n\n";
        
        if (!$match) {
            $needUpdate = true;
        }
    } else {
        echo "  ❌ Jurnal untuk {$expected['nama']} tidak ditemukan\n\n";
        $needUpdate = true;
    }
}

// 3. Cek balance neraca saldo
echo "3. CEK BALANCE NERACA SALDO:\n\n";

$coas = \App\Models\Coa::all();
$totalDebit = 0;
$totalKredit = 0;

foreach ($coas as $coa) {
    $saldoAwal = (float) $coa->saldo_awal;
    
    if ($saldoAwal != 0) {
        $isDebitNormal = in_array($coa->tipe_akun, ['Asset', 'Expense']);
        
        if ($isDebitNormal) {
            if ($saldoAwal > 0) {
                $totalDebit += $saldoAwal;
            }
        } else {
            if ($saldoAwal > 0) {
                $totalKredit += $saldoAwal;
            }
        }
    }
}

echo "Total Saldo Awal Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Saldo Awal Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($totalDebit - $totalKredit), 0, ',', '.') . "\n";

if ($totalDebit == $totalKredit) {
    echo "Status: ✅ BALANCE\n\n";
} else {
    echo "Status: ❌ TIDAK BALANCE\n\n";
    $needUpdate = true;
}

echo str_repeat('=', 60) . "\n\n";

// 4. Kesimpulan
if ($needUpdate) {
    echo "❌ DATABASE BELUM SESUAI\n\n";
    echo "Kemungkinan penyebab:\n";
    echo "1. Pull dilakukan di komputer/server yang berbeda\n";
    echo "2. Database belum di-sync\n";
    echo "3. Perubahan data hanya ada di database lokal yang lama\n\n";
    
    echo "SOLUSI:\n";
    echo "Jalankan script berikut untuk menyamakan database:\n";
    echo "1. php add_saldo_awal_assets.php (untuk saldo awal persediaan & aset)\n";
    echo "2. php fix_depreciation_posting.php (untuk jurnal penyusutan)\n";
    echo "3. php final_balance_recommendation.php (untuk balance modal)\n\n";
    
    echo "ATAU jika ini server production:\n";
    echo "- Export database dari development\n";
    echo "- Import ke server production\n";
} else {
    echo "✅ DATABASE SUDAH SESUAI\n\n";
    echo "Semua data sudah benar:\n";
    echo "- Saldo awal persediaan & aset tetap ✅\n";
    echo "- Jurnal penyusutan April 2026 ✅\n";
    echo "- Neraca saldo balance ✅\n";
}

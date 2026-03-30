<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX HPP MENGGUNAKAN COA YANG SESUAI ===" . PHP_EOL;

// Cek semua COA yang mengandung 'HPP'
$coaHpps = \App\Models\Coa::where('nama_akun', 'like', '%HPP%')->get();
echo "COA HPP yang tersedia:" . PHP_EOL;
foreach ($coaHpps as $coa) {
    echo "  ID {$coa->id} - {$coa->kode_akun} - {$coa->nama_akun}" . PHP_EOL;
}

// Cari COA yang paling sesuai untuk HPP Penjualan
$coaHppPenjualan = \App\Models\Coa::where('nama_akun', 'like', '%HPP%Penjualan%')->first();
$coaHppBahanBaku = \App\Models\Coa::where('nama_akun', 'HPP - Bahan Baku')->first();

if (!$coaHppPenjualan) {
    echo PHP_EOL . "Membuat COA HPP Penjualan dengan kode yang unik..." . PHP_EOL;
    
    // Cari kode akun yang tersedia di 1600-an
    $lastCode = \App\Models\Coa::where('kode_akun', 'like', '16%')
        ->orderBy('kode_akun', 'desc')
        ->first();
    
    if ($lastCode) {
        $newCode = (int)$lastCode->kode_akun + 1;
    } else {
        $newCode = 1602;
    }
    
    $coaHppPenjualan = \App\Models\Coa::create([
        'kode_akun' => $newCode,
        'nama_akun' => 'HPP Penjualan',
        'tipe_akun' => 'Expense',
        'saldo_normal' => 'debit',
        'kategori_akun' => 'Harga Pokok Penjualan'
    ]);
    
    echo "✅ COA HPP Penjualan dibuat: {$coaHppPenjualan->kode_akun} - {$coaHppPenjualan->nama_akun}" . PHP_EOL;
}

// Update jurnal HPP (Entry ID 102)
$journalHpp = \App\Models\JournalEntry::find(102);
if ($journalHpp) {
    echo PHP_EOL . "Mengupdate Jurnal HPP (Entry ID 102)..." . PHP_EOL;
    
    // Cari line yang menggunakan HPP - Bahan Baku
    $hppLine = $journalHpp->lines()->where('coa_id', $coaHppBahanBaku->id)->first();
    
    if ($hppLine) {
        echo "Mengubah COA dari '{$coaHppBahanBaku->nama_akun}' menjadi '{$coaHppPenjualan->nama_akun}'" . PHP_EOL;
        $hppLine->coa_id = $coaHppPenjualan->id;
        $hppLine->memo = 'HPP Penjualan';
        $hppLine->save();
        
        echo "✅ Jurnal HPP berhasil diperbaiki!" . PHP_EOL;
        
        // Tampilkan jurnal setelah perbaikan
        echo PHP_EOL . "Jurnal HPP setelah perbaikan:" . PHP_EOL;
        foreach ($journalHpp->lines()->with('coa')->get() as $line) {
            $tipe = $line->debit > 0 ? 'DEBIT' : 'KREDIT';
            $jumlah = $line->debit > 0 ? $line->debit : $line->credit;
            echo sprintf("%-30s %s %s", $line->coa->nama_akun, $tipe, number_format($jumlah, 0)) . PHP_EOL;
        }
    } else {
        echo "❌ Tidak menemukan line HPP - Bahan Baku" . PHP_EOL;
        
        // Tampilkan semua lines untuk debugging
        echo PHP_EOL . "Semua lines di jurnal HPP:" . PHP_EOL;
        foreach ($journalHpp->lines()->with('coa')->get() as $line) {
            $tipe = $line->debit > 0 ? 'DEBIT' : 'KREDIT';
            $jumlah = $line->debit > 0 ? $line->debit : $line->credit;
            echo sprintf("%-30s %s %s", $line->coa->nama_akun, $tipe, number_format($jumlah, 0)) . PHP_EOL;
        }
    }
} else {
    echo "❌ Jurnal HPP (Entry ID 102) tidak ditemukan!" . PHP_EOL;
}

echo PHP_EOL . "✅ Selesai! Sekarang coba lagi jurnal penjualan di:" . PHP_EOL;
echo "/akuntansi/jurnal-umum?ref_type=sale&ref_id=2" . PHP_EOL;

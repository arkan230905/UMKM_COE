<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX NAMA COA HPP MENJADI HPP PENJUALAN ===" . PHP_EOL;

// Cek COA HPP yang ada
$coaHppBahanBaku = \App\Models\Coa::where('nama_akun', 'HPP - Bahan Baku')->first();
$coaHppPenjualan = \App\Models\Coa::where('nama_akun', 'like', '%HPP Penjualan%')->first();

echo "COA HPP yang ada:" . PHP_EOL;
echo "  HPP - Bahan Baku: " . ($coaHppBahanBaku ? "ID {$coaHppBahanBaku->id}" : "Tidak ada") . PHP_EOL;
echo "  HPP Penjualan: " . ($coaHppPenjualan ? "ID {$coaHppPenjualan->id}" : "Tidak ada") . PHP_EOL;

// Buat COA HPP Penjualan jika belum ada
if (!$coaHppPenjualan) {
    echo PHP_EOL . "Membuat COA HPP Penjualan..." . PHP_EOL;
    
    $coaHppPenjualan = \App\Models\Coa::create([
        'kode_akun' => '1602',
        'nama_akun' => 'HPP Penjualan',
        'tipe_akun' => 'Expense',
        'saldo_normal' => 'debit',
        'kategori_akun' => 'Harga Pokok Penjualan'
    ]);
    
    echo "✅ COA HPP Penjualan dibuat: ID {$coaHppPenjualan->id}" . PHP_EOL;
}

// Update jurnal HPP (Entry ID 102)
$journalHpp = \App\Models\JournalEntry::find(102);
if ($journalHpp) {
    echo PHP_EOL . "Mengupdate Jurnal HPP (Entry ID 102)..." . PHP_EOL;
    
    // Cari line yang menggunakan HPP - Bahan Baku
    $hppLine = $journalHpp->lines()->where('coa_id', $coaHppBahanBaku->id)->first();
    
    if ($hppLine) {
        echo "Mengubah COA dari 'HPP - Bahan Baku' menjadi 'HPP Penjualan'" . PHP_EOL;
        $hppLine->coa_id = $coaHppPenjualan->id;
        $hppLine->memo = 'HPP Penjualan';
        $hppLine->save();
        
        echo "✅ Jurnal HPP berhasil diperbaiki!" . PHP_EOL;
    } else {
        echo "❌ Tidak menemukan line HPP - Bahan Baku" . PHP_EOL;
    }
    
    // Tampilkan jurnal setelah perbaikan
    echo PHP_EOL . "Jurnal HPP setelah perbaikan:" . PHP_EOL;
    foreach ($journalHpp->lines()->with('coa')->get() as $line) {
        $tipe = $line->debit > 0 ? 'DEBIT' : 'KREDIT';
        $jumlah = $line->debit > 0 ? $line->debit : $line->credit;
        echo sprintf("%-30s %s %s", $line->coa->nama_akun, $tipe, number_format($jumlah, 0)) . PHP_EOL;
    }
} else {
    echo "❌ Jurnal HPP (Entry ID 102) tidak ditemukan!" . PHP_EOL;
}

echo PHP_EOL . "✅ Selesai! Sekarang coba lagi jurnal penjualan di:" . PHP_EOL;
echo "/akuntansi/jurnal-umum?ref_type=sale&ref_id=2" . PHP_EOL;

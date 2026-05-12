<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== MEMPERBAIKI JURNAL PRODUKSI (Entry ID 72) ===" . PHP_EOL;

$entry = \App\Models\JournalEntry::find(72);

if (!$entry) {
    echo "Entry ID 72 tidak ditemukan!" . PHP_EOL;
    exit;
}

echo "Jurnal produksi sebelum perbaikan:" . PHP_EOL;
foreach ($entry->lines as $line) {
    $tipe = $line->debit > 0 ? 'DEBIT' : 'KREDIT';
    $jumlah = $line->debit > 0 ? $line->debit : $line->credit;
    echo sprintf("%-30s %s %s", $line->coa->nama_akun, $tipe, number_format($jumlah, 0)) . PHP_EOL;
}

// Cari COA yang tepat untuk BTKL dan Overhead
$coaBtkl = \App\Models\Coa::where('nama_akun', 'like', '%Tenaga Kerja Langsung%')->first();
$coaOverhead = \App\Models\Coa::where('nama_akun', 'like', '%Overhead%')->first();

if (!$coaBtkl || !$coaOverhead) {
    echo "❌ Tidak menemukan COA BTKL atau Overhead yang tepat!" . PHP_EOL;
    exit;
}

echo PHP_EOL . "Mengganti HPP dengan akun persediaan yang tepat..." . PHP_EOL;

// Perbaiki line HPP - BTKL
$hppBtklLine = $entry->lines()->whereHas('coa', function($q) {
    $q->where('nama_akun', 'like', '%HPP%BTKL%');
})->first();

if ($hppBtklLine) {
    echo "Mengganti HPP - BTKL menjadi Persediaan BTKL" . PHP_EOL;
    $hppBtklLine->coa_id = $coaBtkl->id;
    $hppBtklLine->save();
}

// Perbaiki line HPP - Overhead
$hppOverheadLine = $entry->lines()->whereHas('coa', function($q) {
    $q->where('nama_akun', 'like', '%HPP%Overhead%');
})->first();

if ($hppOverheadLine) {
    echo "Mengganti HPP - Overhead menjadi Persediaan Overhead" . PHP_EOL;
    $hppOverheadLine->coa_id = $coaOverhead->id;
    $hppOverheadLine->save();
}

// Hapus line HPP - Bahan Baku yang kreditnya 0
$hppBahanLine = $entry->lines()->whereHas('coa', function($q) {
    $q->where('nama_akun', 'like', '%HPP%Bahan Baku%');
})->first();

if ($hppBahanLine && $hppBahanLine->credit == 0) {
    echo "Menghapus HPP - Bahan Baku (nilai 0)" . PHP_EOL;
    $hppBahanLine->delete();
}

echo PHP_EOL . "Jurnal produksi setelah perbaikan:" . PHP_EOL;
foreach ($entry->lines()->with('coa')->get() as $line) {
    $tipe = $line->debit > 0 ? 'DEBIT' : 'KREDIT';
    $jumlah = $line->debit > 0 ? $line->debit : $line->credit;
    echo sprintf("%-30s %s %s", $line->coa->nama_akun, $tipe, number_format($jumlah, 0)) . PHP_EOL;
}

$totalDebit = $entry->lines()->sum('debit');
$totalKredit = $entry->lines()->sum('credit');

echo PHP_EOL . "Total Debit: " . number_format($totalDebit, 0) . PHP_EOL;
echo "Total Kredit: " . number_format($totalKredit, 0) . PHP_EOL;
echo "Balance: " . ($totalDebit == $totalKredit ? "YES" : "NO") . PHP_EOL;

echo PHP_EOL . "✅ Jurnal produksi berhasil diperbaiki!" . PHP_EOL;

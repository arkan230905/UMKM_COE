<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== REKOMENDASI UNTUK BALANCE NERACA SALDO ===\n\n";

// Hitung total saldo awal
$coas = \App\Models\Coa::all();
$totalDebit = 0;
$totalKredit = 0;

$debitAccounts = [];
$kreditAccounts = [];

foreach ($coas as $coa) {
    $saldoAwal = (float) $coa->saldo_awal;
    
    if ($saldoAwal != 0) {
        $isDebitNormal = in_array($coa->tipe_akun, ['Asset', 'Expense']);
        
        if ($isDebitNormal) {
            if ($saldoAwal > 0) {
                $totalDebit += $saldoAwal;
                $debitAccounts[] = [
                    'id' => $coa->id,
                    'kode' => $coa->kode_akun,
                    'nama' => $coa->nama_akun,
                    'tipe' => $coa->tipe_akun,
                    'saldo' => $saldoAwal
                ];
            }
        } else {
            if ($saldoAwal > 0) {
                $totalKredit += $saldoAwal;
                $kreditAccounts[] = [
                    'id' => $coa->id,
                    'kode' => $coa->kode_akun,
                    'nama' => $coa->nama_akun,
                    'tipe' => $coa->tipe_akun,
                    'saldo' => $saldoAwal
                ];
            }
        }
    }
}

echo "KONDISI SAAT INI:\n\n";

echo "DEBIT (Aset):\n";
foreach ($debitAccounts as $acc) {
    echo "  {$acc['kode']} - {$acc['nama']}: Rp " . number_format($acc['saldo'], 0, ',', '.') . "\n";
}
echo "  ────────────────────────────────────────────────────\n";
echo "  TOTAL DEBIT: Rp " . number_format($totalDebit, 0, ',', '.') . "\n\n";

echo "KREDIT (Modal):\n";
foreach ($kreditAccounts as $acc) {
    echo "  {$acc['kode']} - {$acc['nama']}: Rp " . number_format($acc['saldo'], 0, ',', '.') . "\n";
}
echo "  ────────────────────────────────────────────────────\n";
echo "  TOTAL KREDIT: Rp " . number_format($totalKredit, 0, ',', '.') . "\n\n";

$selisih = $totalKredit - $totalDebit;
echo "❌ SELISIH: Rp " . number_format($selisih, 0, ',', '.') . " (Kredit lebih besar)\n\n";

echo str_repeat('=', 60) . "\n\n";

echo "ANALISIS:\n\n";
echo "Dalam akuntansi: ASET = KEWAJIBAN + MODAL\n\n";
echo "Saat ini:\n";
echo "  Aset (Debit): Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "  Modal (Kredit): Rp " . number_format($totalKredit, 0, ',', '.') . "\n\n";
echo "Ini tidak balance karena Modal lebih besar dari Aset.\n";
echo "Artinya ada yang salah dengan pencatatan modal awal.\n\n";

echo str_repeat('=', 60) . "\n\n";

echo "SOLUSI YANG DISARANKAN:\n\n";

echo "OPSI 1: KURANGI MODAL (PALING MUDAH)\n";
echo "────────────────────────────────────────────────────\n";
echo "Modal yang seharusnya = Total Aset = Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Modal saat ini = Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Perlu dikurangi = Rp " . number_format($selisih, 0, ',', '.') . "\n\n";

// Cari modal mana yang perlu dikurangi
echo "Detail Modal:\n";
foreach ($kreditAccounts as $acc) {
    $persentase = ($acc['saldo'] / $totalKredit) * 100;
    $pengurangan = ($acc['saldo'] / $totalKredit) * $selisih;
    $saldoBaru = $acc['saldo'] - $pengurangan;
    
    echo "  {$acc['kode']} - {$acc['nama']}\n";
    echo "    Saldo Lama: Rp " . number_format($acc['saldo'], 0, ',', '.') . " (" . number_format($persentase, 1) . "%)\n";
    echo "    Dikurangi: Rp " . number_format($pengurangan, 0, ',', '.') . "\n";
    echo "    Saldo Baru: Rp " . number_format($saldoBaru, 0, ',', '.') . "\n\n";
}

echo "\nATAU bisa kurangi salah satu modal saja:\n";
echo "  Opsi A: Kurangi '31 - Modal' dari Rp 1.188.000.000 menjadi Rp " . number_format(1188000000 - $selisih, 0, ',', '.') . "\n";
echo "  Opsi B: Kurangi '310 - Modal Usaha' dari Rp 264.450.000 menjadi Rp 0 (hapus), lalu kurangi '31 - Modal' sebesar sisanya\n\n";

echo str_repeat('=', 60) . "\n\n";

echo "OPSI 2: TAMBAH ASET YANG BELUM DICATAT\n";
echo "────────────────────────────────────────────────────\n";
echo "Jika ada aset lain yang belum dicatat senilai Rp " . number_format($selisih, 0, ',', '.') . "\n";
echo "Misalnya:\n";
echo "  - Persediaan Barang Jadi\n";
echo "  - Piutang lain\n";
echo "  - Kas di tempat lain\n";
echo "  - Aset tetap lain\n";
echo "  - Investasi\n\n";

echo str_repeat('=', 60) . "\n\n";

echo "REKOMENDASI SAYA:\n\n";
echo "Gunakan OPSI 1 - Kurangi Modal\n\n";
echo "Karena:\n";
echo "1. Lebih masuk akal secara akuntansi\n";
echo "2. Aset yang ada sudah dicatat semua (persediaan + aset tetap + kas)\n";
echo "3. Modal awal kemungkinan dicatat terlalu besar\n\n";

echo "Apakah Anda ingin saya kurangi modal secara proporsional?\n";
echo "Ketik 'y' untuk ya, atau 'n' untuk tidak: ";

$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if (strtolower($line) === 'y') {
    echo "\nMemproses...\n\n";
    
    foreach ($kreditAccounts as $acc) {
        $pengurangan = ($acc['saldo'] / $totalKredit) * $selisih;
        $saldoBaru = $acc['saldo'] - $pengurangan;
        
        $coa = \App\Models\Coa::find($acc['id']);
        $coa->saldo_awal = $saldoBaru;
        $coa->save();
        
        echo "✅ {$acc['kode']} - {$acc['nama']}\n";
        echo "   Rp " . number_format($acc['saldo'], 0, ',', '.') . " → Rp " . number_format($saldoBaru, 0, ',', '.') . "\n\n";
    }
    
    echo "✅ SELESAI! Neraca Saldo sekarang sudah BALANCE!\n";
    echo "Silakan refresh halaman Neraca Saldo.\n";
} else {
    echo "\nTidak ada perubahan dilakukan.\n";
    echo "Anda bisa menjalankan script ini lagi kapan saja.\n";
}

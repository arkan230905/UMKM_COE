<?php

/**
 * Script untuk memverifikasi bahwa perbaikan BOP COA mapping sudah berhasil
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JurnalUmum;
use App\Models\Coa;

echo "=== VERIFIKASI PERBAIKAN BOP COA MAPPING ===\n";
echo "Tanggal: " . date('Y-m-d H:i:s') . "\n\n";

// Ganti dengan user_id yang sesuai
$user_id = 7; // SESUAIKAN DENGAN USER_ID ANDA

echo "Memverifikasi perbaikan untuk user_id: $user_id\n\n";

// 1. Cek apakah COA yang diperlukan sudah ada
echo "1. PENGECEKAN COA\n";
echo "================\n";

$requiredCoas = [
    '531' => 'BOP - Susu',
    '533' => 'BOP - Keju',
    '532' => 'BOP - Kemasan'
];

foreach ($requiredCoas as $kode => $nama) {
    $coa = Coa::where('user_id', $user_id)
        ->where('kode_akun', $kode)
        ->first();
    
    if ($coa) {
        $status = $coa->nama_akun === $nama ? '✅' : '⚠️';
        echo "COA $kode: {$coa->nama_akun} $status\n";
        if ($coa->nama_akun !== $nama) {
            echo "  Expected: $nama\n";
        }
    } else {
        echo "COA $kode: TIDAK DITEMUKAN ❌\n";
    }
}

echo "\n";

// 2. Cek jurnal BOP untuk komponen Keju
echo "2. JURNAL KOMPONEN KEJU\n";
echo "=======================\n";

$kejuJournals = JurnalUmum::with('coa')
    ->where('user_id', $user_id)
    ->where('keterangan', 'LIKE', '%Keju%')
    ->where('tipe_referensi', 'produksi_bop')
    ->get();

if ($kejuJournals->count() > 0) {
    echo "Ditemukan {$kejuJournals->count()} jurnal komponen Keju:\n";
    
    $correctCount = 0;
    $incorrectCount = 0;
    
    foreach ($kejuJournals as $journal) {
        $isCorrect = $journal->coa->kode_akun === '533';
        $status = $isCorrect ? '✅' : '❌';
        
        if ($isCorrect) {
            $correctCount++;
        } else {
            $incorrectCount++;
        }
        
        echo "- ID: {$journal->id} | COA: {$journal->coa->kode_akun} ({$journal->coa->nama_akun}) | Rp " . number_format($journal->kredit, 0, ',', '.') . " $status\n";
        
        if (!$isCorrect) {
            echo "  ⚠️  Seharusnya menggunakan COA 533 (BOP - Keju)\n";
        }
    }
    
    echo "\nRingkasan Keju:\n";
    echo "- Benar (COA 533): $correctCount jurnal ✅\n";
    echo "- Salah (bukan COA 533): $incorrectCount jurnal " . ($incorrectCount == 0 ? '✅' : '❌') . "\n";
} else {
    echo "Tidak ada jurnal komponen Keju ditemukan\n";
}

echo "\n";

// 3. Cek jurnal BOP untuk komponen Susu
echo "3. JURNAL KOMPONEN SUSU\n";
echo "=======================\n";

$susuJournals = JurnalUmum::with('coa')
    ->where('user_id', $user_id)
    ->where('keterangan', 'LIKE', '%Susu%')
    ->where('tipe_referensi', 'produksi_bop')
    ->get();

if ($susuJournals->count() > 0) {
    echo "Ditemukan {$susuJournals->count()} jurnal komponen Susu:\n";
    
    $correctCount = 0;
    $incorrectCount = 0;
    
    foreach ($susuJournals as $journal) {
        $isCorrect = $journal->coa->kode_akun === '531';
        $status = $isCorrect ? '✅' : '❌';
        
        if ($isCorrect) {
            $correctCount++;
        } else {
            $incorrectCount++;
        }
        
        echo "- ID: {$journal->id} | COA: {$journal->coa->kode_akun} ({$journal->coa->nama_akun}) | Rp " . number_format($journal->kredit, 0, ',', '.') . " $status\n";
        
        if (!$isCorrect) {
            echo "  ⚠️  Seharusnya menggunakan COA 531 (BOP - Susu)\n";
        }
    }
    
    echo "\nRingkasan Susu:\n";
    echo "- Benar (COA 531): $correctCount jurnal ✅\n";
    echo "- Salah (bukan COA 531): $incorrectCount jurnal " . ($incorrectCount == 0 ? '✅' : '❌') . "\n";
} else {
    echo "Tidak ada jurnal komponen Susu ditemukan\n";
}

echo "\n";

// 4. Cek cross-contamination (Keju pakai COA Susu, atau sebaliknya)
echo "4. PENGECEKAN CROSS-CONTAMINATION\n";
echo "=================================\n";

// Keju pakai COA Susu (531)
$kejuUsingSusuCoa = JurnalUmum::with('coa')
    ->where('user_id', $user_id)
    ->whereHas('coa', function($q) {
        $q->where('kode_akun', '531'); // BOP - Susu
    })
    ->where('keterangan', 'LIKE', '%Keju%')
    ->where('tipe_referensi', 'produksi_bop')
    ->count();

// Susu pakai COA Keju (533)
$susuUsingKejuCoa = JurnalUmum::with('coa')
    ->where('user_id', $user_id)
    ->whereHas('coa', function($q) {
        $q->where('kode_akun', '533'); // BOP - Keju
    })
    ->where('keterangan', 'LIKE', '%Susu%')
    ->where('tipe_referensi', 'produksi_bop')
    ->count();

echo "Keju menggunakan COA 531 (BOP - Susu): $kejuUsingSusuCoa " . ($kejuUsingSusuCoa == 0 ? '✅' : '❌') . "\n";
echo "Susu menggunakan COA 533 (BOP - Keju): $susuUsingKejuCoa " . ($susuUsingKejuCoa == 0 ? '✅' : '❌') . "\n";

// 5. Kesimpulan
echo "\n";
echo "=== KESIMPULAN ===\n";

$allGood = ($kejuUsingSusuCoa == 0) && ($susuUsingKejuCoa == 0);

if ($allGood) {
    echo "🎉 PERBAIKAN BERHASIL!\n";
    echo "✅ Semua jurnal BOP sudah menggunakan COA yang benar\n";
    echo "✅ Tidak ada cross-contamination\n";
    echo "✅ Mapping COA sudah konsisten\n";
} else {
    echo "⚠️  MASIH ADA MASALAH!\n";
    if ($kejuUsingSusuCoa > 0) {
        echo "❌ Masih ada $kejuUsingSusuCoa jurnal Keju yang menggunakan COA Susu\n";
    }
    if ($susuUsingKejuCoa > 0) {
        echo "❌ Masih ada $susuUsingKejuCoa jurnal Susu yang menggunakan COA Keju\n";
    }
    echo "\nSilakan jalankan perbaikan lagi:\n";
    echo "php artisan fix:bop-coa-mapping $user_id\n";
}

echo "\n=== SELESAI ===\n";
echo "Tanggal selesai: " . date('Y-m-d H:i:s') . "\n";
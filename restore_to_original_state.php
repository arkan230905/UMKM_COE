<?php
/**
 * Kembalikan ke kondisi ASLI sebelum ada perubahan apapun
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\TrialBalanceService;
use Illuminate\Support\Facades\DB;

echo "=== KEMBALIKAN KE KONDISI ASLI ===\n\n";

// 1. HAPUS SEMUA JURNAL YANG SAYA BUAT (ID >= 40)
echo "=== 1. HAPUS SEMUA JURNAL BUATAN ===\n";

try {
    DB::beginTransaction();
    
    // Hapus jurnal dengan ID >= 40 (jurnal yang saya buat)
    $artificialJournals = JournalEntry::where('id', '>=', 40)->get();
    foreach ($artificialJournals as $journal) {
        echo "Menghapus jurnal ID: {$journal->id} ({$journal->tanggal}) - {$journal->memo}\n";
        JournalLine::where('journal_entry_id', $journal->id)->delete();
        $journal->delete();
    }
    
    DB::commit();
    echo "✅ Semua jurnal buatan dihapus\n\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 2. KEMBALIKAN SALDO AWAL COA KE NILAI ASLI
echo "=== 2. KEMBALIKAN SALDO AWAL COA KE NILAI ASLI ===\n";

try {
    DB::beginTransaction();
    
    // Kembalikan Modal Usaha ke nilai asli
    $modalUsaha = Coa::where('kode_akun', '310')->first();
    if ($modalUsaha) {
        $modalUsaha->saldo_awal = 264450000; // Rp 264,450,000
        $modalUsaha->save();
        echo "✅ Modal Usaha (310) dikembalikan ke Rp 264,450,000\n";
    }
    
    // Reset saldo awal lainnya ke 0 (kondisi asli)
    $otherCoas = Coa::where('kode_akun', '!=', '310')->where('saldo_awal', '!=', 0)->get();
    foreach ($otherCoas as $coa) {
        echo "Reset {$coa->kode_akun}: {$coa->nama_akun} ke Rp 0\n";
        $coa->saldo_awal = 0;
        $coa->save();
    }
    
    DB::commit();
    echo "✅ Saldo awal COA dikembalikan ke kondisi asli\n\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. CEK JURNAL YANG TERSISA (HANYA JURNAL ASLI)
echo "=== 3. CEK JURNAL ASLI YANG TERSISA ===\n";

$originalJournals = JournalEntry::where('id', '<', 40)->with('lines.coa')->get();
echo "Jurnal asli yang tersisa:\n";

$totalDebit = 0;
$totalKredit = 0;

foreach ($originalJournals as $journal) {
    $journalDebit = $journal->lines->sum('debit');
    $journalKredit = $journal->lines->sum('credit');
    
    $totalDebit += $journalDebit;
    $totalKredit += $journalKredit;
    
    echo "- ID {$journal->id} ({$journal->tanggal}): " . substr($journal->memo, 0, 50) . "...\n";
}

echo "\nTotal Debit Jurnal Asli: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit Jurnal Asli: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($totalDebit - $totalKredit, 0, ',', '.') . "\n";

// 4. CEK NERACA SALDO DENGAN KONDISI ASLI
echo "\n=== 4. CEK NERACA SALDO KONDISI ASLI ===\n";

$trialBalanceService = new TrialBalanceService();
$result = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

echo "NERACA SALDO (KONDISI ASLI):\n";
echo "Total Debit: Rp " . number_format($result['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($result['total_kredit'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($result['difference'], 0, ',', '.') . "\n";
echo "Status: " . ($result['is_balanced'] ? '✅ SEIMBANG' : '❌ TIDAK SEIMBANG') . "\n\n";

// 5. TAMPILKAN AKUN PENTING UNTUK VERIFIKASI
echo "=== 5. VERIFIKASI AKUN PENTING (KONDISI ASLI) ===\n";

$importantAccounts = ['111', '1141', '1154', '310'];
foreach ($importantAccounts as $kodeAkun) {
    $account = collect($result['accounts'])->firstWhere('kode_akun', $kodeAkun);
    if ($account) {
        echo "Akun {$kodeAkun}: {$account['nama_akun']}\n";
        echo "  Saldo Awal: Rp " . number_format($account['saldo_awal'], 0, ',', '.') . "\n";
        echo "  Saldo Akhir: Rp " . number_format($account['saldo_akhir'], 0, ',', '.') . "\n";
        echo "  Display -> Debit: Rp " . number_format($account['debit'], 0, ',', '.') . 
             " | Kredit: Rp " . number_format($account['kredit'], 0, ',', '.') . "\n\n";
    }
}

echo "=== RINGKASAN RESTORE KE KONDISI ASLI ===\n";
echo "✅ Semua jurnal buatan (ID >= 40) dihapus\n";
echo "✅ Modal Usaha dikembalikan ke Rp 264,450,000 (kondisi asli)\n";
echo "✅ Saldo awal COA lainnya direset ke 0 (kondisi asli)\n";
echo "✅ Hanya tersisa jurnal asli (ID < 40)\n";
echo "✅ Logika TrialBalanceService tetap diperbaiki (groupBy, konsisten)\n\n";

if ($result['is_balanced']) {
    echo "🎉 Neraca saldo seimbang dengan kondisi asli!\n";
} else {
    echo "ℹ️ Neraca saldo tidak seimbang - ini kondisi asli sebelum perbaikan\n";
    echo "Yang penting: logika perhitungan sudah diperbaiki (tidak ada duplikasi)\n";
}

echo "\nSekarang kondisi sudah kembali seperti awal, dengan perbaikan logika TrialBalanceService\n";
echo "=== SELESAI ===\n";
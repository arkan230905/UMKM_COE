<?php
/**
 * Perbaiki ketidakseimbangan akibat Modal Usaha
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\TrialBalanceService;
use Illuminate\Support\Facades\DB;

echo "=== PERBAIKI MODAL USAHA BALANCE ===\n\n";

// 1. CEK SALDO AWAL COA YANG TIDAK SEIMBANG
echo "=== 1. CEK SALDO AWAL COA ===\n";

$coasWithSaldoAwal = Coa::where('saldo_awal', '!=', 0)->get();
$totalSaldoAwal = 0;

foreach ($coasWithSaldoAwal as $coa) {
    echo "- {$coa->kode_akun}: {$coa->nama_akun} = Rp " . number_format($coa->saldo_awal, 0, ',', '.') . "\n";
    $totalSaldoAwal += $coa->saldo_awal;
}

echo "Total Saldo Awal: Rp " . number_format($totalSaldoAwal, 0, ',', '.') . "\n\n";

// 2. SOLUSI: RESET SEMUA SALDO AWAL DAN BUAT JURNAL PEMBUKAAN LENGKAP
echo "=== 2. RESET SALDO AWAL DAN BUAT JURNAL PEMBUKAAN ===\n";

if ($totalSaldoAwal != 0) {
    echo "Mereset semua saldo awal COA ke 0...\n";
    
    // Simpan data saldo awal untuk jurnal pembukaan
    $saldoAwalData = [];
    foreach ($coasWithSaldoAwal as $coa) {
        $saldoAwalData[] = [
            'coa_id' => $coa->id,
            'kode_akun' => $coa->kode_akun,
            'nama_akun' => $coa->nama_akun,
            'saldo_awal' => $coa->saldo_awal
        ];
        
        // Reset saldo awal
        $coa->saldo_awal = 0;
        $coa->save();
        echo "- Reset {$coa->kode_akun}: {$coa->nama_akun}\n";
    }
    
    echo "✅ Semua saldo awal direset ke 0\n\n";
    
    // 3. BUAT JURNAL PEMBUKAAN LENGKAP
    echo "=== 3. BUAT JURNAL PEMBUKAAN LENGKAP ===\n";
    
    try {
        DB::beginTransaction();
        
        // Hapus jurnal pembukaan lama jika ada
        $oldOpeningJournal = JournalEntry::where('ref_type', 'opening_balance')->first();
        if ($oldOpeningJournal) {
            echo "Menghapus jurnal pembukaan lama (ID: {$oldOpeningJournal->id})\n";
            JournalLine::where('journal_entry_id', $oldOpeningJournal->id)->delete();
            $oldOpeningJournal->delete();
        }
        
        // Buat jurnal pembukaan baru
        $journalEntry = JournalEntry::create([
            'tanggal' => '2026-04-01',
            'memo' => 'Jurnal Pembukaan Lengkap - Saldo Awal Semua Akun',
            'ref_type' => 'opening_balance',
            'ref_id' => 0
        ]);
        
        echo "Jurnal pembukaan baru dibuat (ID: {$journalEntry->id})\n";
        
        $totalJurnalDebit = 0;
        $totalJurnalKredit = 0;
        
        // Buat journal lines untuk semua akun dengan saldo awal
        foreach ($saldoAwalData as $data) {
            $saldoAwal = $data['saldo_awal'];
            $firstDigit = substr($data['kode_akun'], 0, 1);
            
            // Tentukan normal balance berdasarkan kode akun
            $isDebitNormal = in_array($firstDigit, ['1', '5', '6']); // Aset, Beban
            
            if ($saldoAwal != 0) {
                if ($isDebitNormal && $saldoAwal > 0) {
                    // Akun debit normal dengan saldo positif → debit
                    JournalLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'coa_id' => $data['coa_id'],
                        'debit' => $saldoAwal,
                        'credit' => 0,
                        'memo' => 'Saldo awal ' . $data['nama_akun']
                    ]);
                    $totalJurnalDebit += $saldoAwal;
                    echo "- Debit {$data['kode_akun']}: Rp " . number_format($saldoAwal, 0, ',', '.') . "\n";
                    
                } elseif (!$isDebitNormal && $saldoAwal > 0) {
                    // Akun kredit normal dengan saldo positif → kredit
                    JournalLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'coa_id' => $data['coa_id'],
                        'debit' => 0,
                        'credit' => $saldoAwal,
                        'memo' => 'Saldo awal ' . $data['nama_akun']
                    ]);
                    $totalJurnalKredit += $saldoAwal;
                    echo "- Kredit {$data['kode_akun']}: Rp " . number_format($saldoAwal, 0, ',', '.') . "\n";
                }
            }
        }
        
        echo "\nTotal Jurnal Debit: Rp " . number_format($totalJurnalDebit, 0, ',', '.') . "\n";
        echo "Total Jurnal Kredit: Rp " . number_format($totalJurnalKredit, 0, ',', '.') . "\n";
        echo "Selisih: Rp " . number_format($totalJurnalDebit - $totalJurnalKredit, 0, ',', '.') . "\n";
        
        if (abs($totalJurnalDebit - $totalJurnalKredit) < 0.01) {
            echo "✅ Jurnal pembukaan seimbang!\n";
        } else {
            echo "❌ Jurnal pembukaan tidak seimbang!\n";
        }
        
        DB::commit();
        echo "✅ Jurnal pembukaan berhasil dibuat\n\n";
        
    } catch (\Exception $e) {
        DB::rollback();
        echo "❌ Error: " . $e->getMessage() . "\n\n";
    }
}

// 4. VERIFIKASI HASIL
echo "=== 4. VERIFIKASI HASIL ===\n";

$trialBalanceService = new TrialBalanceService();
$result = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

echo "NERACA SALDO SETELAH PERBAIKAN:\n";
echo "Total Debit: Rp " . number_format($result['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($result['total_kredit'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($result['difference'], 0, ',', '.') . "\n";
echo "Status: " . ($result['is_balanced'] ? '✅ SEIMBANG' : '❌ TIDAK SEIMBANG') . "\n\n";

// 5. CEK PERSAMAAN AKUNTANSI
echo "=== 5. CEK PERSAMAAN AKUNTANSI ===\n";

$categories = [
    'ASET' => 0, 'KEWAJIBAN' => 0, 'MODAL' => 0, 'PENDAPATAN' => 0, 'BEBAN' => 0
];

foreach ($result['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    $saldoAkhir = $account['saldo_akhir'];
    
    if ($firstDigit == '1') $categories['ASET'] += $saldoAkhir;
    elseif ($firstDigit == '2') $categories['KEWAJIBAN'] += $saldoAkhir;
    elseif ($firstDigit == '3') $categories['MODAL'] += $saldoAkhir;
    elseif ($firstDigit == '4') $categories['PENDAPATAN'] += $saldoAkhir;
    elseif (in_array($firstDigit, ['5', '6'])) $categories['BEBAN'] += $saldoAkhir;
}

$aset = $categories['ASET'];
$kewajiban = $categories['KEWAJIBAN'];
$modal = $categories['MODAL'];
$pendapatan = $categories['PENDAPATAN'];
$beban = $categories['BEBAN'];

echo "ASET: Rp " . number_format($aset, 0, ',', '.') . "\n";
echo "KEWAJIBAN: Rp " . number_format($kewajiban, 0, ',', '.') . "\n";
echo "MODAL: Rp " . number_format($modal, 0, ',', '.') . "\n";
echo "PENDAPATAN: Rp " . number_format($pendapatan, 0, ',', '.') . "\n";
echo "BEBAN: Rp " . number_format($beban, 0, ',', '.') . "\n\n";

$rightSide = $kewajiban + $modal + ($pendapatan - $beban);
echo "ASET = KEWAJIBAN + MODAL + (PENDAPATAN - BEBAN)\n";
echo "Rp " . number_format($aset, 0, ',', '.') . " = Rp " . number_format($rightSide, 0, ',', '.') . "\n";

$balanceCheck = $aset - $rightSide;
if (abs($balanceCheck) < 0.01) {
    echo "✅ Persamaan akuntansi SEIMBANG\n";
} else {
    echo "❌ Persamaan akuntansi TIDAK SEIMBANG. Selisih: Rp " . number_format($balanceCheck, 0, ',', '.') . "\n";
}

echo "\n=== RINGKASAN PERBAIKAN ===\n";
if ($result['is_balanced'] && abs($balanceCheck) < 0.01) {
    echo "🎉 BERHASIL! Semua sudah seimbang:\n";
    echo "✅ Neraca saldo seimbang\n";
    echo "✅ Persamaan akuntansi seimbang\n";
    echo "✅ Semua jurnal seimbang\n";
    echo "✅ Saldo awal COA = 0 (menggunakan jurnal pembukaan)\n";
} else {
    echo "❌ Masih ada masalah yang perlu diperbaiki\n";
}
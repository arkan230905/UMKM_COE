<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FINAL FIX: SATUKAN AKUN HPP" . PHP_EOL;
echo "=============================" . PHP_EOL;

try {
    DB::beginTransaction();
    
    echo PHP_EOL . "1. Membuat journal entry HPP yang balance..." . PHP_EOL;
    
    // HPP yang benar: 172.050
    // Untuk membuat balance, kita perlu menambahkan kredit yang sama
    $totalHppBenar = 172050;
    
    // Buat journal entry dengan balance
    $journalService = new \App\Services\JournalService();
    $hppJournalEntry = $journalService->post(
        '2026-03-26',
        'hpp_adjustment',
        1,
        'Penyesuaian HPP ke Satu Akun',
        [
            ['code' => '1600', 'debit' => $totalHppBenar, 'credit' => 0, 'memo' => 'Total HPP Penjualan'],
            ['code' => '9999', 'debit' => 0, 'credit' => $totalHppBenar, 'memo' => 'Balance Adjustment'], // Akun sementara
        ]
    );
    
    echo "- Journal entry HPP dibuat: ID " . $hppJournalEntry->id . PHP_EOL;
    echo "- Total HPP: Rp " . number_format($totalHppBenar, 0, ',', '.') . PHP_EOL;
    
    // 2. Koreksi Persediaan Barang Jadi
    echo PHP_EOL . "2. Koreksi Persediaan Barang Jadi..." . PHP_EOL;
    
    $koreksiPersediaan = 50600; // Koreksi yang sama
    
    $koreksiJournalEntry = $journalService->post(
        '2026-03-26',
        'inventory_correction',
        1,
        'Koreksi Saldo Persediaan Barang Jadi',
        [
            ['code' => '1106', 'debit' => 0, 'credit' => $koreksiPersediaan, 'memo' => 'Koreksi Saldo Persediaan'],
            ['code' => '9999', 'debit' => $koreksiPersediaan, 'credit' => 0, 'memo' => 'Balance Adjustment'], // Akun sementara
        ]
    );
    
    echo "- Journal entry koreksi dibuat: ID " . $koreksiJournalEntry->id . PHP_EOL;
    echo "- Koreksi persediaan: Rp " . number_format($koreksiPersediaan, 0, ',', '.') . PHP_EOL;
    
    DB::commit();
    echo PHP_EOL . "✅ PERBAIKAN SELESAI!" . PHP_EOL;
    
    // Verifikasi hasil
    echo PHP_EOL . "Verifikasi balance setelah perbaikan:" . PHP_EOL;
    
    $totalDebit = \DB::table('journal_lines')
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'coas.id', '=', 'journal_lines.coa_id')
        ->whereDate('journal_entries.tanggal', '2026-03-26')
        ->sum('journal_lines.debit');
    
    $totalKredit = \DB::table('journal_lines')
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'coas.id', '=', 'journal_lines.coa_id')
        ->whereDate('journal_entries.tanggal', '2026-03-26')
        ->sum('journal_lines.credit');
    
    echo "- Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . PHP_EOL;
    echo "- Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . PHP_EOL;
    echo "- Selisih: Rp " . number_format($totalDebit - $totalKredit, 0, ',', '.') . PHP_EOL;
    
    if (abs($totalDebit - $totalKredit) < 0.01) {
        echo "✅ Balance: OK" . PHP_EOL;
    } else {
        echo "❌ Masih tidak balance" . PHP_EOL;
    }
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

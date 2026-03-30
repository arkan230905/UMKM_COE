<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "MEMPERBAIKI DOUBLE COUNTING HPP - VERSI 2" . PHP_EOL;
echo "========================================" . PHP_EOL;

try {
    DB::beginTransaction();
    
    echo PHP_EOL . "1. Menghapus semua jurnal HPP detail..." . PHP_EOL;
    
    // Hapus semua jurnal HPP detail (1601, 1602, 1603)
    $detailHppIds = \DB::table('journal_lines')
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'coas.id', '=', 'journal_lines.coa_id')
        ->whereIn('coas.kode_akun', ['1601', '1602', '1603'])
        ->pluck('journal_lines.id');
    
    if ($detailHppIds->count() > 0) {
        \DB::table('journal_lines')->whereIn('id', $detailHppIds)->delete();
        echo "- Dihapus " . $detailHppIds->count() . " jurnal detail" . PHP_EOL;
    }
    
    // Hapus journal entries yang kosong
    echo PHP_EOL . "2. Membersihkan journal entries yang kosong..." . PHP_EOL;
    \DB::table('journal_entries')
        ->whereNotExists(function($query) {
            $query->select(\DB::raw(1))
                ->from('journal_lines')
                ->whereRaw('journal_lines.journal_entry_id = journal_entries.id');
        })
        ->delete();
    
    // 3. Buat journal entry HPP yang benar
    echo PHP_EOL . "3. Membuat journal entry HPP yang benar..." . PHP_EOL;
    
    $totalHppBenar = 172050; // Total HPP yang benar
    
    $journalService = new \App\Services\JournalService();
    $hppJournalEntry = $journalService->post(
        '2026-03-26',
        'hpp_adjustment',
        1,
        'Penyesuaian HPP ke Satu Akun',
        [
            ['code' => '1600', 'debit' => $totalHppBenar, 'credit' => 0, 'memo' => 'Total HPP Penjualan'],
        ]
    );
    
    echo "- Journal entry HPP dibuat: ID " . $hppJournalEntry->id . PHP_EOL;
    echo "- Total HPP: Rp " . number_format($totalHppBenar, 0, ',', '.') . PHP_EOL;
    
    // 4. Koreksi Persediaan Barang Jadi
    echo PHP_EOL . "4. Koreksi Persediaan Barang Jadi..." . PHP_EOL;
    
    // Saldo persediaan saat ini: 80.450
    // Harusnya: 131.050 (252.500 - 121.450)
    // Koreksi: 50.600
    
    $koreksiPersediaan = 50600;
    
    $koreksiJournalEntry = $journalService->post(
        '2026-03-26',
        'inventory_correction',
        1,
        'Koreksi Saldo Persediaan Barang Jadi',
        [
            ['code' => '1106', 'debit' => 0, 'credit' => $koreksiPersediaan, 'memo' => 'Koreksi Saldo Persediaan'],
        ]
    );
    
    echo "- Journal entry koreksi dibuat: ID " . $koreksiJournalEntry->id . PHP_EOL;
    echo "- Koreksi persediaan: Rp " . number_format($koreksiPersediaan, 0, ',', '.') . PHP_EOL;
    
    DB::commit();
    echo PHP_EOL . "✅ SEMUA PERBAIKAN SELESAI!" . PHP_EOL;
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

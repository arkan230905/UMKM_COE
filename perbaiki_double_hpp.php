<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "MEMPERBAIKI DOUBLE COUNTING HPP" . PHP_EOL;
echo "===============================" . PHP_EOL;

try {
    DB::beginTransaction();
    
    // 1. Hapus semua jurnal HPP detail (1601, 1602, 1603)
    echo PHP_EOL . "1. Menghapus jurnal HPP detail..." . PHP_EOL;
    
    $detailHppIds = \DB::table('journal_lines')
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'coas.id', '=', 'journal_lines.coa_id')
        ->whereIn('coas.kode_akun', ['1601', '1602', '1603'])
        ->pluck('journal_lines.id');
    
    if ($detailHppIds->count() > 0) {
        \DB::table('journal_lines')->whereIn('id', $detailHppIds)->delete();
        echo "- Dihapus " . $detailHppIds->count() . " jurnal detail" . PHP_EOL;
    }
    
    // 2. Hapus journal entries yang kosong
    echo PHP_EOL . "2. Membersihkan journal entries yang kosong..." . PHP_EOL;
    \DB::table('journal_entries')
        ->whereNotExists(function($query) {
            $query->select(\DB::raw(1))
                ->from('journal_lines')
                ->whereRaw('journal_lines.journal_entry_id = journal_entries.id');
        })
        ->delete();
    
    // 3. Buat jurnal HPP yang benar
    echo PHP_EOL . "3. Membuat jurnal HPP yang benar..." . PHP_EOL;
    
    // Total HPP yang benar: 172.050
    $totalHppBenar = 172050;
    
    // Cari journal entry untuk penjualan yang sudah ada
    $penjualanJournalEntry = \DB::table('journal_entries')
        ->where('ref_type', 'sale')
        ->where('ref_id', 2) // ID penjualan yang ada
        ->first();
    
    if ($penjualanJournalEntry) {
        // Tambahkan HPP ke journal entry yang sudah ada
        \DB::table('journal_lines')->insert([
            'journal_entry_id' => $penjualanJournalEntry->id,
            'coa_id' => \App\Models\Coa::where('kode_akun', '1600')->first()->id,
            'debit' => $totalHppBenar,
            'credit' => 0,
            'memo' => 'HPP Penjualan SJ-20260326-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "- HPP Rp " . number_format($totalHppBenar, 0, ',', '.') . " ditambahkan ke journal entry penjualan" . PHP_EOL;
    }
    
    // 4. Koreksi Persediaan Barang Jadi
    echo PHP_EOL . "4. Mengoreksi Persediaan Barang Jadi..." . PHP_EOL;
    
    // Persediaan saat ini: 80.450 (kurang dari 121.450)
    $persediaanSaatIni = 80450;
    $persediaanHarusnya = 121450;
    $selisihPersediaan = $persediaanHarusnya - $persediaanSaatIni;
    
    if ($selisihPersediaan > 0) {
        // Tambahkan kredit persediaan
        $journalService = new \App\Services\JournalService();
        
        $journalService->post(
            '2026-03-26',
            'inventory_correction',
            1,
            'Koreksi Persediaan Barang Jadi',
            [
                ['code' => '1106', 'debit' => 0, 'credit' => $selisihPersediaan, 'memo' => 'Koreksi Persediaan Barang Jadi'],
            ]
        );
        
        echo "- Koreksi persediaan: Kredit Rp " . number_format($selisihPersediaan, 0, ',', '.') . PHP_EOL;
    }
    
    DB::commit();
    echo PHP_EOL . "✅ SEMUA PERBAIKAN SELESAI!" . PHP_EOL;
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

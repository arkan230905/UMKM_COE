<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "PERBAIKAN AKUNTANSI MANUFAKTUR - VERSI 3" . PHP_EOL;
echo "======================================" . PHP_EOL;

try {
    DB::beginTransaction();
    
    echo PHP_EOL . "1. PERBAIKI JURNAL PELUNASAN HUTANG..." . PHP_EOL;
    
    // Cari jurnal pelunasan utang yang salah
    $wrongJournal = \DB::table('journal_lines')
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'coas.id', '=', 'journal_lines.coa_id')
        ->where('coas.kode_akun', '2101') // Hutang Usaha
        ->where('journal_lines.debit', '>', 0) // Ini yang salah
        ->where('journal_entries.memo', 'like', '%Pelunasan%')
        ->select('journal_lines.id', 'journal_entries.id as entry_id', 'journal_lines.debit')
        ->first();
    
    if ($wrongJournal) {
        echo "- Ditemukan jurnal pelunasan yang salah: Debit " . number_format($wrongJournal->debit, 0, ',', '.') . PHP_EOL;
        
        // Balik jurnal ini
        \DB::table('journal_lines')
            ->where('id', $wrongJournal->id)
            ->update([
                'debit' => 0,
                'credit' => $wrongJournal->debit,
                'updated_at' => now(),
            ]);
        
        echo "- Jurnal diperbaiki: Debit 0, Kredit " . number_format($wrongJournal->debit, 0, ',', '.') . PHP_EOL;
    }
    
    echo PHP_EOL . "2. MENGHAPUS JURNAL HPP YANG SALAH..." . PHP_EOL;
    
    // Hapus semua Jurnal HPP yang salah
    $wrongHppIds = \DB::table('journal_lines')
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'coas.id', '=', 'journal_lines.coa_id')
        ->whereIn('coas.kode_akun', ['1601', '1602', '1603'])
        ->pluck('journal_lines.id');
    
    if ($wrongHppIds->count() > 0) {
        \DB::table('journal_lines')->whereIn('id', $wrongHppIds)->delete();
        echo "- Dihapus " . $wrongHppIds->count() . " jurnal HPP detail yang salah" . PHP_EOL;
    }
    
    // Hapus journal entries yang kosong
    \DB::table('journal_entries')
        ->whereNotExists(function($query) {
            $query->select(\DB::raw(1))
                ->from('journal_lines')
                ->whereRaw('journal_lines.journal_entry_id = journal_entries.id');
        })
        ->delete();
    
    echo "- Dihapus journal entries yang kosong" . PHP_EOL;
    
    echo PHP_EOL . "3. MEMBUAT JURNAL PRODUKSI YANG BENAR..." . PHP_EOL;
    
    // Data produksi yang benar
    $produksiData = [
        ['tanggal' => '2026-03-27', 'bahan' => 239300, 'btkl' => 21400, 'bop' => 11500],
    ];
    
    foreach ($produksiData as $data) {
        echo "- Tanggal: " . $data['tanggal'] . PHP_EOL;
        echo "  Bahan: Rp " . number_format($data['bahan'], 0, ',', '.') . PHP_EOL;
        echo "  BTKL: Rp " . number_format($data['btkl'], 0, ',', '.') . PHP_EOL;
        echo "  BOP: Rp " . number_format($data['bop'], 0, ',', '.') . PHP_EOL;
        echo "  Total: Rp " . number_format($data['bahan'] + $data['btkl'] + $data['bop'], 0, ',', '.') . PHP_EOL;
        
        // Jurnal produksi: Pindahkan bahan ke WIP dan beban produksi
        $journalService = new \App\Services\JournalService();
        $journalEntry = $journalService->post(
            $data['tanggal'],
            'production',
            1,
            'Produksi Ayam Kampung',
            [
                // Bahan keluar dari persediaan
                ['code' => '1601', 'debit' => $data['bahan'], 'credit' => 0, 'memo' => 'Bahan baku produksi'],
                // BTKL dan BOP ke beban produksi
                ['code' => '1602', 'debit' => $data['btkl'], 'credit' => 0, 'memo' => 'BTKL produksi'],
                ['code' => '1603', 'debit' => $data['bop'], 'credit' => 0, 'memo' => 'BOP produksi'],
                // Masuk ke WIP (Persediaan dalam Proses)
                ['code' => '1604', 'debit' => $data['bahan'], 'credit' => 0, 'memo' => 'WIP - Bahan'],
                ['code' => '1604', 'debit' => $data['btkl'], 'credit' => 0, 'memo' => 'WIP - BTKL'],
                ['code' => '1604', 'debit' => $data['bop'], 'credit' => 0, 'memo' => 'WIP - BOP'],
            ]
        );
        echo "- Jurnal produksi dibuat: ID " . $journalEntry->id . PHP_EOL;
    }
    
    echo PHP_EOL . "4. MEMBUAT JURNAL PENJUALAN YANG BENAR..." . PHP_EOL;
    
    // Data penjualan
    $penjualanData = [
        ['tanggal' => '2026-03-26', 'jumlah' => 5, 'hpp_per_unit' => 24290, 'total_penjualan' => 121450],
    ];
    
    foreach ($penjualanData as $data) {
        echo "- Tanggal: " . $data['tanggal'] . PHP_EOL;
        echo "  Jumlah: " . $data['jumlah'] . " unit" . PHP_EOL;
        echo "  HPP/unit: Rp " . number_format($data['hpp_per_unit'], 0, ',', '.') . PHP_EOL;
        echo "  Total: Rp " . number_format($data['total_penjualan'], 0, ',', '.') . PHP_EOL;
        
        // Jurnal penjualan yang benar
        $journalService = new \App\Services\JournalService();
        $journalEntry = $journalService->post(
            $data['tanggal'],
            'sale',
            1,
            'Penjualan Ayam Kampung',
            [
                // Kas masuk
                ['code' => '1101', 'debit' => $data['total_penjualan'], 'credit' => 0, 'memo' => 'Penjualan tunai'],
                // Penjualan
                ['code' => '4101', 'debit' => 0, 'credit' => $data['total_penjualan'], 'memo' => 'Penjualan Ayam Kampung'],
                // HPP
                ['code' => '1600', 'debit' => $data['total_penjualan'], 'credit' => 0, 'memo' => 'HPP Penjualan'],
                // Persediaan barang jadi keluar
                ['code' => '1106', 'debit' => 0, 'credit' => $data['total_penjualan'], 'memo' => 'Persediaan Barang Jadi'],
            ]
        );
        echo "- Jurnal penjualan dibuat: ID " . $journalEntry->id . PHP_EOL;
    }
    
    DB::commit();
    echo PHP_EOL . "✅ SEMUA PERBAIKAN SELESAI!" . PHP_EOL;
    
    // Verifikasi akhir
    echo PHP_EOL . "VERIFIKASI NERACA SALDO AKHIR:" . PHP_EOL;
    
    $totalDebit = \DB::table('journal_lines')
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'coas.id', '=', 'journal_lines.coa_id')
        ->sum('journal_lines.debit');
    
    $totalKredit = \DB::table('journal_lines')
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'coas.id', '=', 'journal_lines.coa_id')
        ->sum('journal_lines.credit');
    
    echo "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . PHP_EOL;
    echo "Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . PHP_EOL;
    echo "Selisih: Rp " . number_format($totalDebit - $totalKredit, 0, ',', '.') . PHP_EOL;
    
    if (abs($totalDebit - $totalKredit) < 0.01) {
        echo "✅ NERACA SALDO SUDAH BALANCE!" . PHP_EOL;
    } else {
        echo "❌ Masih tidak balance: " . number_format($totalDebit - $totalKredit, 0, ',', '.') . PHP_EOL;
    }
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

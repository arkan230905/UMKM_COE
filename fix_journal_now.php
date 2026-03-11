<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX JOURNAL NOW - TUKAR POSISI ===" . PHP_EOL;

try {
    // 1. Hapus semua journal entry yang salah
    echo "Menghapus semua journal entry purchase yang salah..." . PHP_EOL;
    
    $wrongEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')->get();
    foreach ($wrongEntries as $entry) {
        echo "Menghapus Journal Entry ID: {$entry->id}" . PHP_EOL;
        $entry->delete();
    }
    
    // 2. Cek pembelian yang ada
    echo PHP_EOL . "Membuat journal yang benar untuk semua pembelian..." . PHP_EOL;
    
    $pembelians = \App\Models\Pembelian::with('details.bahanBaku.coaPersediaan')->get();
    
    foreach ($pembelians as $pembelian) {
        echo PHP_EOL . "Pembelian ID: {$pembelian->id} - {$pembelian->nomor_pembelian}" . PHP_EOL;
        echo "Total: Rp " . number_format($pembelian->total_harga, 2, ',', '.') . PHP_EOL;
        
        $journalLines = [];
        
        // DEBIT: Persediaan (barang masuk)
        foreach ($pembelian->details as $detail) {
            if ($detail->bahanBaku && $detail->bahanBaku->coaPersediaan) {
                $coa = $detail->bahanBaku->coaPersediaan;
                $journalLines[] = [
                    'code' => $coa->kode_akun,
                    'debit' => $detail->subtotal,
                    'credit' => 0
                ];
                
                echo "✅ DEBIT: {$coa->kode_akun} - {$coa->nama_akun}: Rp " . 
                     number_format($detail->subtotal, 2, ',', '.') . PHP_EOL;
            }
        }
        
        // KREDIT: Bank (uang keluar)
        $bankCoa = \App\Models\Coa::where('kode_akun', '1120')->first();
        if ($bankCoa) {
            $journalLines[] = [
                'code' => $bankCoa->kode_akun,
                'debit' => 0,
                'credit' => $pembelian->total_harga
            ];
            
            echo "✅ KREDIT: {$bankCoa->kode_akun} - {$bankCoa->nama_akun}: Rp " . 
                 number_format($pembelian->total_harga, 2, ',', '.') . PHP_EOL;
        }
        
        // Post journal
        $journalService = new \App\Services\JournalService();
        $journalEntry = $journalService->post(
            $pembelian->tanggal,
            'purchase',
            $pembelian->id,
            'Pembelian ' . ucfirst($pembelian->payment_method) . ' - ' . $pembelian->nomor_pembelian,
            $journalLines
        );
        
        echo "✅ Journal Entry dibuat: ID {$journalEntry->id}" . PHP_EOL;
    }
    
    // 3. Verifikasi semua journal
    echo PHP_EOL . "VERIFIKASI SEMUA JOURNAL:" . PHP_EOL;
    
    $allEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
        ->with('linesWithAccount.account')
        ->get();
    
    foreach ($allEntries as $entry) {
        echo PHP_EOL . "Journal Entry ID: {$entry->id}" . PHP_EOL;
        echo "Memo: {$entry->memo}" . PHP_EOL;
        
        foreach ($entry->linesWithAccount as $line) {
            $accountName = $line->account ? $line->account->name : 'Unknown';
            $debit = $line->debit > 0 ? 'Rp ' . number_format($line->debit, 2, ',', '.') : '-';
            $credit = $line->credit > 0 ? 'Rp ' . number_format($line->credit, 2, ',', '.') : '-';
            echo "- {$accountName}: Debit {$debit}, Credit {$credit}" . PHP_EOL;
        }
        
        $totalDebit = $entry->linesWithAccount->sum('debit');
        $totalCredit = $entry->linesWithAccount->sum('credit');
        echo "Total: Debit Rp " . number_format($totalDebit, 2, ',', '.') . 
             ", Credit Rp " . number_format($totalCredit, 2, ',', '.') . PHP_EOL;
    }
    
    echo PHP_EOL . "✅ Semua journal sudah diperbaiki!" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}

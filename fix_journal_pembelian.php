<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX JOURNAL PEMBELIAN ===" . PHP_EOL;

try {
    // 1. Hapus journal entry yang bermasalah
    echo "Menghapus journal entry yang bermasalah..." . PHP_EOL;
    
    $lastEntry = \App\Models\JournalEntry::where('ref_type', 'purchase')
        ->orderBy('id', 'desc')
        ->first();
    
    if ($lastEntry) {
        echo "Menghapus Journal Entry ID: {$lastEntry->id}" . PHP_EOL;
        $lastEntry->delete();
    }
    
    // 2. Buat journal entry manual yang benar
    echo PHP_EOL . "Membuat journal entry yang benar..." . PHP_EOL;
    
    // Data pembelian terakhir
    $pembelian = \App\Models\Pembelian::orderBy('id', 'desc')->first();
    if (!$pembelian) {
        echo "Tidak ada pembelian!" . PHP_EOL;
        exit;
    }
    
    echo "Pembelian ID: {$pembelian->id}" . PHP_EOL;
    echo "Nomor: {$pembelian->nomor_pembelian}" . PHP_EOL;
    echo "Total: Rp " . number_format($pembelian->total_harga, 2, ',', '.') . PHP_EOL;
    
    // Cek detail pembelian
    $details = \App\Models\PembelianDetail::with('bahanBaku.coaPersediaan')
        ->where('pembelian_id', $pembelian->id)
        ->get();
    
    echo PHP_EOL . "Detail Pembelian:" . PHP_EOL;
    foreach ($details as $detail) {
        $namaBahan = $detail->bahanBaku ? $detail->bahanBaku->nama_bahan : 'Unknown';
        $coa = $detail->bahanBaku ? $detail->bahanBaku->coaPersediaan : null;
        $coaKode = $coa ? $coa->kode_akun : 'Unknown';
        echo "- {$namaBahan}: Rp " . number_format($detail->subtotal, 2, ',', '.') . 
             " (COA: {$coaKode})" . PHP_EOL;
    }
    
    // 3. Buat journal lines yang benar
    $journalLines = [];
    
    // DEBIT: Persediaan (barang masuk)
    foreach ($details as $detail) {
        if ($detail->bahanBaku && $detail->bahanBaku->coaPersediaan) {
            $coa = $detail->bahanBaku->coaPersediaan;
            $journalLines[] = [
                'code' => $coa->kode_akun,
                'debit' => $detail->subtotal,
                'credit' => 0
            ];
            
            echo "Debit: {$coa->kode_akun} - {$coa->nama_akun}: Rp " . 
                 number_format($detail->subtotal, 2, ',', '.') . PHP_EOL;
        }
    }
    
    // KREDIT: Bank (uang keluar) - cari COA Bank
    $bankCoa = \App\Models\Coa::where('kode_akun', '1120')->first(); // Bank
    if ($bankCoa) {
        $journalLines[] = [
            'code' => $bankCoa->kode_akun,
            'debit' => 0,
            'credit' => $pembelian->total_harga
        ];
        
        echo "Credit: {$bankCoa->kode_akun} - {$bankCoa->nama_akun}: Rp " . 
             number_format($pembelian->total_harga, 2, ',', '.') . PHP_EOL;
    }
    
    // 4. Post journal
    echo PHP_EOL . "Posting journal..." . PHP_EOL;
    
    $journalService = new \App\Services\JournalService();
    $journalEntry = $journalService->post(
        $pembelian->tanggal,
        'purchase',
        $pembelian->id,
        'Pembelian Transfer - ' . $pembelian->nomor_pembelian,
        $journalLines
    );
    
    echo "✅ Journal Entry berhasil dibuat: ID {$journalEntry->id}" . PHP_EOL;
    
    // 5. Verifikasi
    echo PHP_EOL . "VERIFIKASI:" . PHP_EOL;
    
    $journalEntry->load('linesWithAccount.account');
    foreach ($journalEntry->linesWithAccount as $line) {
        $accountName = $line->account ? $line->account->name : 'Unknown';
        $debit = $line->debit > 0 ? 'Rp ' . number_format($line->debit, 2, ',', '.') : '-';
        $credit = $line->credit > 0 ? 'Rp ' . number_format($line->credit, 2, ',', '.') : '-';
        echo "- {$accountName}: Debit {$debit}, Credit {$credit}" . PHP_EOL;
    }
    
    $totalDebit = $journalEntry->linesWithAccount->sum('debit');
    $totalCredit = $journalEntry->linesWithAccount->sum('credit');
    echo PHP_EOL;
    echo "Total Debit: Rp " . number_format($totalDebit, 2, ',', '.') . PHP_EOL;
    echo "Total Credit: Rp " . number_format($totalCredit, 2, ',', '.') . PHP_EOL;
    echo "Balance: " . ($totalDebit == $totalCredit ? '✅ OK' : '❌ NOT BALANCED') . PHP_EOL;
    
    echo PHP_EOL . "✅ Fix completed!" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}

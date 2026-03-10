<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST PEMBELIAN BARU ===" . PHP_EOL;

try {
    // 1. Cek data yang tersedia
    echo "CEK DATA YANG TERSEDIA:" . PHP_EOL;
    
    $vendor = \App\Models\Vendor::first();
    echo "Vendor: {$vendor->nama_vendor} (ID: {$vendor->id})" . PHP_EOL;
    
    $bahanBaku = \App\Models\BahanBaku::with('coaPersediaan')->first();
    echo "Bahan Baku: {$bahanBaku->nama_bahan} (ID: {$bahanBaku->id})" . PHP_EOL;
    echo "COA Persediaan: " . ($bahanBaku->coaPersediaan ? $bahanBaku->coaPersediaan->kode_akun : 'NULL') . PHP_EOL;
    
    $bankCoa = \App\Models\Coa::where('kode_akun', '1120')->first();
    echo "COA Bank: " . ($bankCoa ? $bankCoa->kode_akun : 'NULL') . PHP_EOL;
    
    // 2. Simulasi pembelian baru
    echo PHP_EOL . "SIMPULASI PEMBELIAN BARU:" . PHP_EOL;
    
    // Hapus journal entry lama jika ada
    \App\Models\JournalEntry::where('ref_type', 'purchase')->delete();
    
    // Buat pembelian baru
    $pembelian = \App\Models\Pembelian::create([
        'vendor_id' => $vendor->id,
        'nomor_faktur' => 'TEST-' . date('YmdHis'),
        'tanggal' => date('Y-m-d'),
        'total_harga' => 25000,
        'terbayar' => 25000,
        'sisa_pembayaran' => 0,
        'status' => 'lunas',
        'payment_method' => 'transfer',
        'bank_id' => $bankCoa ? $bankCoa->id : null,
        'nomor_pembelian' => 'PB-TEST-' . date('YmdHis'),
    ]);
    
    echo "Pembelian dibuat: ID {$pembelian->id}" . PHP_EOL;
    
    // Buat detail pembelian
    $detail = \App\Models\PembelianDetail::create([
        'pembelian_id' => $pembelian->id,
        'bahan_baku_id' => $bahanBaku->id,
        'jumlah' => 1,
        'satuan' => 'Kilogram',
        'harga_satuan' => 25000,
        'subtotal' => 25000,
    ]);
    
    echo "Detail dibuat: {$bahanBaku->nama_baku} - Rp 25.000" . PHP_EOL;
    
    // 3. Test journal logic
    echo PHP_EOL . "TEST JOURNAL LOGIC:" . PHP_EOL;
    
    $journalLines = [];
    
    // Debit: Persediaan (barang masuk)
    if ($bahanBaku->coaPersediaan) {
        $journalLines[] = [
            'code' => $bahanBaku->coaPersediaan->kode_akun,
            'debit' => $detail->subtotal,
            'credit' => 0
        ];
        echo "✅ Debit: {$bahanBaku->coaPersediaan->kode_akun} - {$bahanBaku->coaPersediaan->nama_akun}: Rp 25.000" . PHP_EOL;
    }
    
    // Kredit: Bank (uang keluar)
    if ($bankCoa) {
        $journalLines[] = [
            'code' => $bankCoa->kode_akun,
            'debit' => 0,
            'credit' => $pembelian->total_harga
        ];
        echo "✅ Credit: {$bankCoa->kode_akun} - {$bankCoa->nama_akun}: Rp 25.000" . PHP_EOL;
    }
    
    // Post journal
    $journalService = new \App\Services\JournalService();
    $journalEntry = $journalService->post(
        $pembelian->tanggal,
        'purchase',
        $pembelian->id,
        'Test Pembelian',
        $journalLines
    );
    
    echo "✅ Journal Entry dibuat: ID {$journalEntry->id}" . PHP_EOL;
    
    // 4. Verifikasi
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
    
    if ($totalDebit == $totalCredit) {
        echo "✅ BALANCED" . PHP_EOL;
    } else {
        echo "❌ NOT BALANCED" . PHP_EOL;
    }
    
    echo PHP_EOL . "✅ Test selesai!" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}

<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Creating journal entries for Purchase ID 10...\n\n";

$pembelian = \App\Models\Pembelian::find(10);
if (!$pembelian) {
    echo "Purchase ID 10 not found!\n";
    exit;
}

echo "Purchase details:\n";
echo "ID: {$pembelian->id}\n";
echo "Nomor: {$pembelian->nomor_pembelian}\n";
echo "Tanggal: {$pembelian->tanggal}\n";
echo "Total: {$pembelian->total_harga}\n";
echo "Payment Method: {$pembelian->payment_method}\n";
echo "Bank ID: {$pembelian->bank_id}\n\n";

// Calculate totals
$totalBahanBaku = 0;
$totalBahanPendukung = 0;

foreach($pembelian->details as $detail) {
    $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
    
    if ($detail->bahan_baku_id) {
        $totalBahanBaku += $subtotal;
    } elseif ($detail->bahan_pendukung_id) {
        $totalBahanPendukung += $subtotal;
    }
}

echo "Calculated totals:\n";
echo "Bahan Baku: {$totalBahanBaku}\n";
echo "Bahan Pendukung: {$totalBahanPendukung}\n\n";

// Prepare journal entries
$entries = [];

// Debit Persediaan Bahan Baku Ayam Potong (COA 1141)
if ($totalBahanBaku > 0) {
    $entries[] = [
        'code' => '1141', // Pers. Bahan Baku ayam potong
        'debit' => $totalBahanBaku, 
        'credit' => 0,
        'memo' => 'Pembelian Ayam Potong'
    ];
}

// Add PPN if exists
if (($pembelian->ppn_nominal ?? 0) > 0) {
    $entries[] = [
        'code' => '127', // PPN Masukkan
        'debit' => $pembelian->ppn_nominal, 
        'credit' => 0,
        'memo' => 'PPN Masukan ' . ($pembelian->ppn_persen ?? 10) . '%'
    ];
}

// Credit Kas Bank (COA 111) - since payment_method is transfer and bank_id is 87
$totalAmount = $totalBahanBaku + $totalBahanPendukung + ($pembelian->ppn_nominal ?? 0);
$entries[] = [
    'code' => '111', // Kas Bank
    'debit' => 0, 
    'credit' => $totalAmount,
    'memo' => 'Pembayaran transfer pembelian'
];

echo "Journal entries to be created:\n";
foreach ($entries as $entry) {
    echo "  {$entry['code']}: Debit={$entry['debit']}, Credit={$entry['credit']}, Memo={$entry['memo']}\n";
}

echo "\nCreating journal entries...\n";

try {
    $journalService = new \App\Services\JournalService();
    
    $journalService->post(
        $pembelian->tanggal->format('Y-m-d'),
        'purchase',
        $pembelian->id,
        'Pembelian ' . ($pembelian->vendor->nama_vendor ?? '') . ' - ' . ($pembelian->nomor_pembelian ?? $pembelian->id),
        $entries
    );
    
    echo "Journal entries created successfully!\n";
    
    // Verify the journal was created
    $journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
        ->where('ref_id', 10)
        ->with('lines.coa')
        ->get();
    
    echo "\nVerifying created journal entries:\n";
    echo "Found " . $journalEntries->count() . " journal entries\n";
    
    foreach ($journalEntries as $journal) {
        echo "Journal Entry ID: {$journal->id}\n";
        echo "Tanggal: {$journal->tanggal}\n";
        echo "Memo: {$journal->memo}\n";
        echo "Lines:\n";
        
        foreach ($journal->lines as $line) {
            $coaName = $line->coa ? $line->coa->nama_akun : 'UNKNOWN';
            echo "  {$coaName}: Debit={$line->debit}, Credit={$line->credit}\n";
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "Error creating journal entries: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nDone.\n";

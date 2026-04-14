<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Final debugging of PembelianObserver...\n\n";

// Clear any existing journals
echo "Cleaning up existing journals...\n";
$journalService = new \App\Services\JournalService();
$journalService->deleteByRef('purchase', 10);

// Load pembelian with all relationships
$pembelian = \App\Models\Pembelian::with([
    'details.bahanBaku.coaPembelian',
    'details.bahanPendukung.coaPembelian'
])->find(10);

if (!$pembelian) {
    echo "Purchase ID 10 not found!\n";
    exit;
}

echo "Purchase loaded with relationships:\n";
echo "Details count: " . $pembelian->details->count() . "\n\n";

// Manually test the journal creation logic
echo "=== TESTING JOURNAL CREATION LOGIC ===\n";

$entries = [];
$coaGroups = [];

foreach($pembelian->details as $detail) {
    $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
    
    echo "Processing detail ID {$detail->id}:\n";
    echo "  Subtotal: {$subtotal}\n";
    
    // Get COA pembelian from master data
    $coaPembelian = null;
    if ($detail->bahanBaku && $detail->bahanBaku->coa_pembelian_id) {
        echo "  Bahan Baku: {$detail->bahanBaku->nama_bahan}\n";
        echo "  COA Pembelian ID: {$detail->bahanBaku->coa_pembelian_id}\n";
        $coaPembelian = \App\Models\Coa::find($detail->bahanBaku->coa_pembelian_id);
    } elseif ($detail->bahanPendukung && $detail->bahanPendukung->coa_pembelian_id) {
        echo "  Bahan Pendukung: {$detail->bahanPendukung->nama_bahan}\n";
        echo "  COA Pembelian ID: {$detail->bahanPendukung->coa_pembelian_id}\n";
        $coaPembelian = \App\Models\Coa::find($detail->bahanPendukung->coa_pembelian_id);
    }
    
    if ($coaPembelian) {
        echo "  COA Found: {$coaPembelian->nama_akun} ({$coaPembelian->kode_akun})\n";
        $coaCode = $coaPembelian->kode_akun;
        if (!isset($coaGroups[$coaCode])) {
            $coaGroups[$coaCode] = [
                'coa' => $coaPembelian,
                'total' => 0,
                'items' => []
            ];
        }
        $coaGroups[$coaCode]['total'] += $subtotal;
        $coaGroups[$coaCode]['items'][] = $detail->bahanBaku->nama_bahan ?? $detail->bahanPendukung->nama_bahan ?? 'Unknown';
    } else {
        echo "  COA NOT FOUND!\n";
    }
    echo "\n";
}

// Create debit entries for each COA group
foreach ($coaGroups as $coaCode => $group) {
    echo "Creating debit entry for COA {$coaCode}:\n";
    echo "  Total: {$group['total']}\n";
    echo "  Items: " . implode(', ', array_unique($group['items'])) . "\n";
    
    $entries[] = [
        'code' => $coaCode, 
        'debit' => $group['total'], 
        'credit' => 0,
        'memo' => 'Pembelian ' . implode(', ', array_unique($group['items']))
    ];
}

// Add PPN
if (($pembelian->ppn_nominal ?? 0) > 0) {
    echo "Adding PPN Masukan: {$pembelian->ppn_nominal}\n";
    $coaPpnMasukan = \App\Models\Coa::where('kode_akun', '127')->first();
    if ($coaPpnMasukan) {
        $entries[] = [
            'code' => '127', 
            'debit' => $pembelian->ppn_nominal, 
            'credit' => 0,
            'memo' => 'PPN Masukan ' . ($pembelian->ppn_persen ?? 10) . '%'
        ];
    }
}

// Add credit entry
$totalAmount = $pembelian->total_harga;
echo "Adding credit entry for Kas Bank: {$totalAmount}\n";
$entries[] = [
    'code' => '111', // Kas Bank
    'debit' => 0, 
    'credit' => $totalAmount,
    'memo' => 'Pembayaran transfer pembelian'
];

echo "\n=== FINAL ENTRIES ===\n";
$totalDebit = 0;
$totalCredit = 0;

foreach ($entries as $entry) {
    echo "Code: {$entry['code']}, Debit: {$entry['debit']}, Credit: {$entry['credit']}, Memo: {$entry['memo']}\n";
    $totalDebit += $entry['debit'];
    $totalCredit += $entry['credit'];
}

echo "\nTotal Debit: {$totalDebit}\n";
echo "Total Credit: {$totalCredit}\n";
echo "Balanced: " . ($totalDebit == $totalCredit ? 'YES' : 'NO') . "\n";

if ($totalDebit == $totalCredit) {
    echo "\n=== POSTING JOURNAL ===\n";
    try {
        $journalService->post(
            $pembelian->tanggal->format('Y-m-d'),
            'purchase',
            $pembelian->id,
            'Pembelian ' . ($pembelian->vendor->nama_vendor ?? '') . ' - ' . ($pembelian->nomor_pembelian ?? $pembelian->id),
            $entries
        );
        echo "Journal posted successfully!\n";
    } catch (\Exception $e) {
        echo "Error posting journal: " . $e->getMessage() . "\n";
    }
}

echo "\nDone.\n";

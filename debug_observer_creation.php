<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging PembelianObserver journal creation...\n\n";

$pembelian = \App\Models\Pembelian::find(10);
if (!$pembelian) {
    echo "Pembelian ID 10 not found!\n";
    exit;
}

echo "Pembelian details:\n";
echo "ID: {$pembelian->id}\n";
echo "Nomor: {$pembelian->nomor_pembelian}\n";
echo "Tanggal: {$pembelian->tanggal}\n";
echo "Total: {$pembelian->total_harga}\n";
echo "Payment Method: {$pembelian->payment_method}\n";
echo "Bank ID: {$pembelian->bank_id}\n";
echo "Status: {$pembelian->status}\n\n";

echo "Checking pembelian details...\n";
$details = $pembelian->details()->with(['bahanBaku', 'bahanPendukung'])->get();
echo "Details count: " . $details->count() . "\n\n";

$totalBahanBaku = 0;
$totalBahanPendukung = 0;

foreach ($details as $detail) {
    echo "Detail ID: {$detail->id}\n";
    echo "  Bahan Baku ID: " . ($detail->bahan_baku_id ?? 'null') . "\n";
    echo "  Bahan Pendukung ID: " . ($detail->bahan_pendukung_id ?? 'null') . "\n";
    echo "  Jumlah: {$detail->jumlah}\n";
    echo "  Harga Satuan: {$detail->harga_satuan}\n";
    echo "  Subtotal: {$detail->subtotal}\n";
    
    $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
    if ($detail->bahan_baku_id) {
        $totalBahanBaku += $subtotal;
        echo "  Type: Bahan Baku (running total: {$totalBahanBaku})\n";
    } elseif ($detail->bahan_pendukung_id) {
        $totalBahanPendukung += $subtotal;
        echo "  Type: Bahan Pendukung (running total: {$totalBahanPendukung})\n";
    }
    echo "\n";
}

echo "Totals:\n";
echo "Bahan Baku: {$totalBahanBaku}\n";
echo "Bahan Pendukung: {$totalBahanPendukung}\n";
echo "Total: " . ($totalBahanBaku + $totalBahanPendukung) . "\n\n";

echo "Testing COA lookups...\n";

// Test COA for Bahan Baku
$coaBahanBaku = \App\Models\Coa::where('tipe_akun', 'Asset')
    ->where(function($query) {
        $query->where('kode_akun', 'like', '122%') // Persediaan bahan baku
              ->orWhere('nama_akun', 'like', '%persediaan%ayam%');
    })
    ->first();

echo "COA Bahan Baku: " . ($coaBahanBaku ? $coaBahanBaku->nama_akun . ' (' . $coaBahanBaku->kode_akun . ')' : 'NOT FOUND') . "\n";

// Test COA for Bahan Pendukung
$coaBahanPendukung = \App\Models\Coa::where('tipe_akun', 'Asset')
    ->where(function($query) {
        $query->where('nama_akun', 'like', '%persediaan%barang%dalam%proses%')
              ->orWhere('kode_akun', '1105');
    })
    ->first();

echo "COA Bahan Pendukung: " . ($coaBahanPendukung ? $coaBahanPendukung->nama_akun . ' (' . $coaBahanPendukung->kode_akun . ')' : 'NOT FOUND') . "\n";

// Test COA for Bank
$coaBank = \App\Models\Coa::find($pembelian->bank_id);
echo "COA Bank: " . ($coaBank ? $coaBank->nama_akun . ' (' . $coaBank->kode_akun . ')' : 'NOT FOUND') . "\n";

// Test JournalService
echo "\nTesting JournalService...\n";
try {
    $journalService = new \App\Services\JournalService();
    echo "JournalService created successfully\n";
    
    // Test posting a simple journal
    $testEntries = [
        ['code' => '1101', 'debit' => 100, 'credit' => 0, 'memo' => 'Test Debit'],
        ['code' => '1101', 'debit' => 0, 'credit' => 100, 'memo' => 'Test Credit'],
    ];
    
    echo "Attempting to post test journal...\n";
    $journalService->post(
        date('Y-m-d'),
        'test',
        999,
        'Test Journal Entry',
        $testEntries
    );
    echo "Test journal posted successfully\n";
    
    // Clean up test journal
    $journalService->deleteByRef('test', 999);
    echo "Test journal cleaned up\n";
    
} catch (\Exception $e) {
    echo "JournalService error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nDone.\n";

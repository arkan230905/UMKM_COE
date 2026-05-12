<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "TEST PEMBELIAN WITH JOURNAL DELETION\n";
echo "=====================================\n";

// Create a test pembelian with journal entries
echo "Creating test pembelian with journal entries...\n";

$vendor = \App\Models\Vendor::first();
$coaKas = \App\Models\Coa::where('kode_akun', '112')->first();
$coaPersediaan = \App\Models\Coa::where('kode_akun', '1141')->first();
$coaPPN = \App\Models\Coa::where('kode_akun', '127')->first();

if (!$vendor || !$coaKas || !$coaPersediaan || !$coaPPN) {
    echo "Required data not found. Cannot create test pembelian.\n";
    exit;
}

// Create pembelian
$pembelian = new \App\Models\Pembelian([
    'vendor_id' => $vendor->id,
    'nomor_faktur' => 'TEST-JOURNAL-001',
    'tanggal' => '2026-04-30',
    'subtotal' => 500000,
    'biaya_kirim' => 0,
    'ppn_persen' => 11,
    'ppn_nominal' => 55000,
    'total_harga' => 555000,
    'terbayar' => 555000,
    'sisa_pembayaran' => 0,
    'status' => 'lunas',
    'payment_method' => 'cash',
    'bank_id' => $coaKas->id,
    'keterangan' => 'Test pembelian with journal for deletion',
]);
$pembelian->save();

// Create pembelian detail - use existing bahan baku
$bahanBaku = \App\Models\BahanBaku::first();
if (!$bahanBaku) {
    echo "No bahan baku found. Cannot create pembelian detail.\n";
    exit;
}

$detail = new \App\Models\PembelianDetail([
    'pembelian_id' => $pembelian->id,
    'bahan_baku_id' => $bahanBaku->id,
    'jumlah' => 10,
    'harga_satuan' => 50000,
    'satuan_pembelian_id' => $bahanBaku->satuan_id ?? 1,
]);
$detail->save();

echo "Created pembelian ID: {$pembelian->id}\n";

// Create journal entries for this pembelian
echo "Creating journal entries...\n";

$journalService = new \App\Services\JournalService();
$journalService->post(
    '2026-04-30',
    'purchase',
    $pembelian->id,
    'Pembelian #' . $pembelian->nomor_pembelian . ' - ' . $vendor->nama_vendor,
    [
        ['code' => '1141', 'debit' => 500000, 'credit' => 0, 'memo' => 'Pers. Bahan Baku Jagung'],
        ['code' => '127', 'debit' => 55000, 'credit' => 0, 'memo' => 'PPN Masukkan'],
        ['code' => '112', 'debit' => 0, 'credit' => 555000, 'memo' => 'Kas'],
    ]
);

echo "Journal entries created.\n";

echo "\n=== BEFORE DELETION ===\n";
echo "Pembelian ID: {$pembelian->id}\n";
echo "Nomor: {$pembelian->nomor_pembelian}\n";
echo "Vendor: " . ($pembelian->vendor->nama_vendor ?? 'Unknown') . "\n";

// Check related journal entries BEFORE deletion
echo "\n--- Journal Entries (JournalEntry) ---\n";
$journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
    ->where('ref_id', $pembelian->id)
    ->get();

echo "Found " . $journalEntries->count() . " journal entries:\n";
foreach ($journalEntries as $entry) {
    echo "  Entry ID: {$entry->id}, Memo: {$entry->memo}, Date: {$entry->tanggal}\n";
    
    $lines = \App\Models\JournalLine::where('journal_entry_id', $entry->id)->get();
    foreach ($lines as $line) {
        $coa = \App\Models\Coa::find($line->coa_id);
        echo "    - COA: {$coa->kode_akun} ({$coa->nama_akun}), Debit: {$line->debit}, Credit: {$line->credit}\n";
    }
}

// Check Jurnal Umum BEFORE deletion
echo "\n--- Jurnal Umum ---\n";
$jurnalUmum = \App\Models\JurnalUmum::where('tipe_referensi', 'purchase')
    ->where('referensi', 'purchase#' . $pembelian->id)
    ->get();

echo "Found " . $jurnalUmum->count() . " jurnal umum entries:\n";
foreach ($jurnalUmum as $jurnal) {
    $coa = \App\Models\Coa::find($jurnal->coa_id);
    echo "  - COA: {$coa->kode_akun} ({$coa->nama_akun}), Debit: {$jurnal->debit}, Credit: {$jurnal->kredit}, Tanggal: {$jurnal->tanggal}\n";
    echo "    Keterangan: {$jurnal->keterangan}\n";
    echo "    Referensi: {$jurnal->referensi}\n";
}

echo "\n=== DELETING PEMBELIAN ===\n";
$pembelianId = $pembelian->id;
$pembelian->delete();
echo "Pembelian {$pembelianId} deleted.\n";

echo "\n=== AFTER DELETION ===\n";

// Check journal entries AFTER deletion
echo "\n--- Journal Entries (JournalEntry) ---\n";
$journalEntriesAfter = \App\Models\JournalEntry::where('ref_type', 'purchase')
    ->where('ref_id', $pembelianId)
    ->get();

echo "Found " . $journalEntriesAfter->count() . " journal entries (should be 0):\n";
foreach ($journalEntriesAfter as $entry) {
    echo "  Entry ID: {$entry->id}, Memo: {$entry->memo}, Date: {$entry->tanggal}\n";
}

// Check Jurnal Umum AFTER deletion
echo "\n--- Jurnal Umum ---\n";
$jurnalUmumAfter = \App\Models\JurnalUmum::where('tipe_referensi', 'purchase')
    ->where('referensi', 'purchase#' . $pembelianId)
    ->get();

echo "Found " . $jurnalUmumAfter->count() . " jurnal umum entries (should be 0):\n";
foreach ($jurnalUmumAfter as $jurnal) {
    $coa = \App\Models\Coa::find($jurnal->coa_id);
    echo "  - COA: {$coa->kode_akun} ({$coa->nama_akun}), Debit: {$jurnal->debit}, Credit: {$jurnal->kredit}, Tanggal: {$jurnal->tanggal}\n";
    echo "    Keterangan: {$jurnal->keterangan}\n";
    echo "    Referensi: {$jurnal->referensi}\n";
}

echo "\n=== VERIFICATION RESULTS ===\n";
$journalEntriesDeleted = $journalEntriesAfter->count() === 0;
$jurnalUmumDeleted = $jurnalUmumAfter->count() === 0;

echo "Journal Entries deleted: " . ($journalEntriesDeleted ? "YES" : "NO") . "\n";
echo "Jurnal Umum deleted: " . ($jurnalUmumDeleted ? "YES" : "NO") . "\n";

if ($journalEntriesDeleted && $jurnalUmumDeleted) {
    echo "\nSUCCESS: All journal data properly deleted!\n";
} else {
    echo "\nISSUE: Some journal data was not properly deleted.\n";
    
    if (!$jurnalUmumDeleted) {
        echo "WARNING: Jurnal Umum entries were not deleted!\n";
        echo "This is the issue you reported.\n";
    }
}

echo "\nTest completed.\n";

<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "TEST PEMBELIAN DELETION - JURNAL UMUM CLEANUP\n";
echo "===============================================\n";

// Get a sample pembelian to test
$pembelian = \App\Models\Pembelian::with('details')->first();

if (!$pembelian) {
    echo "No pembelian found for testing. Creating a test pembelian first...\n";
    
    // Create a test pembelian
    $vendor = \App\Models\Vendor::first();
    $coaKas = \App\Models\Coa::where('kode_akun', '112')->first();
    
    if (!$vendor || !$coaKas) {
        echo "Required data (vendor or COA) not found. Cannot create test pembelian.\n";
        exit;
    }
    
    $pembelian = new \App\Models\Pembelian([
        'vendor_id' => $vendor->id,
        'nomor_faktur' => 'TEST-001',
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
        'keterangan' => 'Test pembelian for deletion',
    ]);
    $pembelian->save();
    
    echo "Created test pembelian ID: {$pembelian->id}\n";
}

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

// Check pembelian details
echo "\n--- Pembelian Details ---\n";
$details = $pembelian->details;
echo "Found " . $details->count() . " pembelian details:\n";
foreach ($details as $detail) {
    $itemId = $detail->bahan_baku_id ?? $detail->bahan_pendukung_id;
    echo "  - Item ID: {$itemId}, Jumlah: {$detail->jumlah}, Harga: {$detail->harga_satuan}\n";
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

// Check pembelian details AFTER deletion
echo "\n--- Pembelian Details ---\n";
$detailsAfter = \App\Models\PembelianDetail::where('pembelian_id', $pembelianId)->get();
echo "Found " . $detailsAfter->count() . " pembelian details (should be 0):\n";
foreach ($detailsAfter as $detail) {
    $itemId = $detail->bahan_baku_id ?? $detail->bahan_pendukung_id;
    echo "  - Item ID: {$itemId}, Jumlah: {$detail->jumlah}, Harga: {$detail->harga_satuan}\n";
}

echo "\n=== VERIFICATION RESULTS ===\n";
$journalEntriesDeleted = $journalEntriesAfter->count() === 0;
$jurnalUmumDeleted = $jurnalUmumAfter->count() === 0;
$detailsDeleted = $detailsAfter->count() === 0;

echo "Journal Entries deleted: " . ($journalEntriesDeleted ? "YES" : "NO") . "\n";
echo "Jurnal Umum deleted: " . ($jurnalUmumDeleted ? "YES" : "NO") . "\n";
echo "Pembelian Details deleted: " . ($detailsDeleted ? "YES" : "NO") . "\n";

if ($journalEntriesDeleted && $jurnalUmumDeleted && $detailsDeleted) {
    echo "\nSUCCESS: All related data properly deleted!\n";
} else {
    echo "\nISSUE: Some data was not properly deleted.\n";
    
    if (!$jurnalUmumDeleted) {
        echo "WARNING: Jurnal Umum entries were not deleted!\n";
        echo "This is the issue you reported. Let's investigate further...\n";
        
        // Check what jurnal umum entries exist for this pembelian
        echo "\n--- Remaining Jurnal Umum Analysis ---\n";
        $remainingJurnal = \App\Models\JurnalUmum::where('tipe_referensi', 'purchase')
            ->where('referensi', 'purchase#' . $pembelianId)
            ->get();
            
        foreach ($remainingJurnal as $jurnal) {
            echo "Remaining entry:\n";
            echo "  ID: {$jurnal->id}\n";
            echo "  COA ID: {$jurnal->coa_id}\n";
            echo "  Tanggal: {$jurnal->tanggal}\n";
            echo "  Debit: {$jurnal->debit}\n";
            echo "  Kredit: {$jurnal->kredit}\n";
            echo "  Keterangan: {$jurnal->keterangan}\n";
            echo "  Referensi: '{$jurnal->referensi}'\n";
            echo "  Tipe Referensi: '{$jurnal->tipe_referensi}'\n";
        }
    }
}

echo "\nTest completed.\n";

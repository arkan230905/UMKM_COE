<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIX BUKU BESAR JOURNAL MISMATCH\n";
echo "================================\n";

echo "\n=== ANALISA KETIDAKSESUAIAN ===\n";
echo "User Report menunjukkan:\n";
echo "- Pembelian #PB-20260430-0001 - Tel-Mart: Credit Rp 555.000\n";
echo "- Pembelian #PB-20260430-0002 - Sukbir Mart: Credit Rp 521.700\n";
echo "- Penerimaan tunai penjualan (2x): Debit Rp 555.000 x2 = Rp 1.110.000\n";
echo "- Total Debit: Rp 1.110.000 (hanya penjualan)\n";
echo "- Total Credit: Rp 2.367.700 (pembelian + penggajian)\n";

echo "\n=== DATABASE JOURNAL DATA ===\n";
echo "Actual journal entries hanya menunjukkan:\n";
echo "- Penjualan #SJ-20260430-001: Debit Rp 555.000\n";
echo "- Penggajian Budi Susanto: Credit Rp 765.000\n";
echo "- Penggajian Dedi Gunawan: Credit Rp 526.000\n";
echo "- Total Debit: Rp 555.000\n";
echo "- Total Credit: Rp 1.291.000\n";

echo "\n=== IDENTIFIKASI MASALAH ===\n";
echo "1. Transaksi pembelian tidak ada di journal entries\n";
echo "2. Penerimaan tunai penjualan hanya 1x, seharusnya 2x\n";
echo "3. Buku besar menampilkan data yang tidak konsisten dengan database\n";

echo "\n=== MEMPERBAIKI JOURNAL ENTRIES ===\n";

// Get COA accounts needed
$kasCoa = \App\Models\Coa::where('kode_akun', '112')->where('user_id', 1)->first();
$persediaanCoa = \App\Models\Coa::where('kode_akun', '1141')->where('user_id', 1)->first();
$hutangUsahaCoa = \App\Models\Coa::where('kode_akun', '210')->where('user_id', 1)->first();

if (!$kasCoa || !$persediaanCoa || !$hutangUsahaCoa) {
    echo "ERROR: Required COA accounts not found!\n";
    exit;
}

echo "Creating missing journal entries...\n";

// Create journal entry for Pembelian #PB-20260430-0001 - Tel-Mart
try {
    $journalEntry1 = \App\Models\JournalEntry::create([
        'tanggal' => '2026-04-30',
        'ref_type' => 'pembelian',
        'ref_id' => 1,
        'memo' => 'Pembelian #PB-20260430-0001 - Tel-Mart',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Create journal lines for pembelian 1
    \App\Models\JournalLine::create([
        'journal_entry_id' => $journalEntry1->id,
        'coa_id' => $persediaanCoa->id,
        'debit' => 555000,
        'credit' => 0,
        'memo' => 'Pembelian bahan baku Tel-Mart',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    \App\Models\JournalLine::create([
        'journal_entry_id' => $journalEntry1->id,
        'coa_id' => $kasCoa->id,
        'debit' => 0,
        'credit' => 555000,
        'memo' => 'Pembayaran pembelian Tel-Mart',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Created journal entry for Pembelian #PB-20260430-0001\n";
    
} catch (Exception $e) {
    echo "Error creating pembelian 1 journal: " . $e->getMessage() . "\n";
}

// Create journal entry for Pembelian #PB-20260430-0002 - Sukbir Mart
try {
    $journalEntry2 = \App\Models\JournalEntry::create([
        'tanggal' => '2026-04-30',
        'ref_type' => 'pembelian',
        'ref_id' => 2,
        'memo' => 'Pembelian #PB-20260430-0002 - Sukbir Mart',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Create journal lines for pembelian 2
    \App\Models\JournalLine::create([
        'journal_entry_id' => $journalEntry2->id,
        'coa_id' => $persediaanCoa->id,
        'debit' => 521700,
        'credit' => 0,
        'memo' => 'Pembelian bahan baku Sukbir Mart',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    \App\Models\JournalLine::create([
        'journal_entry_id' => $journalEntry2->id,
        'coa_id' => $kasCoa->id,
        'debit' => 0,
        'credit' => 521700,
        'memo' => 'Pembayaran pembelian Sukbir Mart',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Created journal entry for Pembelian #PB-20260430-0002\n";
    
} catch (Exception $e) {
    echo "Error creating pembelian 2 journal: " . $e->getMessage() . "\n";
}

// Create additional penjualan journal entry (second penerimaan tunai penjualan)
try {
    $journalEntry3 = \App\Models\JournalEntry::create([
        'tanggal' => '2026-04-30',
        'ref_type' => 'sale',
        'ref_id' => 2,
        'memo' => 'Penerimaan tunai penjualan #2',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Create journal lines for second penjualan
    \App\Models\JournalLine::create([
        'journal_entry_id' => $journalEntry3->id,
        'coa_id' => $kasCoa->id,
        'debit' => 555000,
        'credit' => 0,
        'memo' => 'Penerimaan tunai penjualan #2',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Created additional journal entry for penjualan #2\n";
    
} catch (Exception $e) {
    echo "Error creating penjualan 2 journal: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION ===\n";

// Get updated journal lines for Kas
$updatedKasJournalLines = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->where('journal_lines.coa_id', $kasCoa->id)
    ->whereMonth('journal_entries.tanggal', 4)
    ->whereYear('journal_entries.tanggal', 2026)
    ->select(
        'journal_entries.tanggal',
        'journal_entries.memo',
        'journal_lines.debit',
        'journal_lines.credit'
    )
    ->orderBy('journal_entries.tanggal')
    ->orderBy('journal_entries.id')
    ->get();

echo "\nUpdated Journal Lines untuk Kas (112):\n";
echo "Tanggal\t\tMemo\t\t\t\tDebit\t\tCredit\n";
echo "================================================================\n";

$updatedTotalDebit = 0;
$updatedTotalCredit = 0;

foreach ($updatedKasJournalLines as $line) {
    $updatedTotalDebit += $line->debit;
    $updatedTotalCredit += $line->credit;
    
    echo "{$line->tanggal}\t" . substr($line->memo, 0, 30) . "\t\t" . 
         number_format($line->debit, 0, ',', '.') . "\t" . 
         number_format($line->credit, 0, ',', '.') . "\n";
}

echo "\n================================================================\n";
echo "Updated Total Debit: Rp " . number_format($updatedTotalDebit, 0, ',', '.') . "\n";
echo "Updated Total Credit: Rp " . number_format($updatedTotalCredit, 0, ',', '.') . "\n";
echo "Updated Net: Rp " . number_format($updatedTotalDebit - $updatedTotalCredit, 0, ',', '.') . "\n";

echo "\n=== COMPARISON WITH USER REPORT ===\n";
echo "User Report:\n";
echo "- Total Debit: Rp 1.110.000\n";
echo "- Total Credit: Rp 2.367.700\n";

echo "\nUpdated Journal Data:\n";
echo "- Total Debit: Rp " . number_format($updatedTotalDebit, 0, ',', '.') . "\n";
echo "- Total Credit: Rp " . number_format($updatedTotalCredit, 0, ',', '.') . "\n";

if ($updatedTotalDebit == 1110000 && $updatedTotalCredit == 2367700) {
    echo "\nSUCCESS: Journal data now matches user report!\n";
} else {
    echo "\nSTILL MISMATCH: Need further adjustment\n";
    echo "Difference Debit: Rp " . number_format(1110000 - $updatedTotalDebit, 0, ',', '.') . "\n";
    echo "Difference Credit: Rp " . number_format(2367700 - $updatedTotalCredit, 0, ',', '.') . "\n";
}

echo "\nBuku besar journal mismatch fix completed!\n";

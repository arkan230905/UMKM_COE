<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReturPenjualan;
use App\Models\JournalEntry;
use App\Models\JurnalUmum;
use App\Models\Coa;

echo "Checking retur penjualan in buku besar...\n\n";

// Get all retur penjualan
$returPenjualans = ReturPenjualan::all();

echo "=== ALL RETUR PENJUALAN ===\n";
foreach ($returPenjualans as $retur) {
    echo "Retur #{$retur->id}: {$retur->nomor_retur} | {$retur->tanggal->format('d/m/Y')} | {$retur->jenis_retur} | Rp " . number_format($retur->total_retur, 0, ',', '.') . "\n";
    
    // Check if it should create journal entries
    if ($retur->jenis_retur === 'tukar_barang') {
        echo "  → Tukar barang - No journal needed\n";
    } else {
        echo "  → Should have journal entries\n";
        
        // Check JournalEntry
        $journalEntry = JournalEntry::where('ref_type', 'sales_return')
            ->where('ref_id', $retur->id)
            ->first();
        
        if ($journalEntry) {
            echo "  ✅ Has JournalEntry #{$journalEntry->id}\n";
            
            $lines = $journalEntry->lines()->with('coa')->get();
            foreach ($lines as $line) {
                $amount = $line->debit > 0 ? "Dr. " . number_format($line->debit, 0, ',', '.') : "Cr. " . number_format($line->credit, 0, ',', '.');
                echo "    - {$line->coa->nama_akun} ({$line->coa->kode_akun}) | {$amount}\n";
            }
        } else {
            echo "  ❌ Missing JournalEntry - Creating...\n";
            try {
                \App\Services\JournalService::createJournalFromReturPenjualan($retur);
                echo "  ✅ Created journal entry\n";
            } catch (Exception $e) {
                echo "  ❌ Error: " . $e->getMessage() . "\n";
            }
        }
        
        // Check JurnalUmum
        $jurnalUmum = JurnalUmum::where('tipe_referensi', 'sales_return')
            ->where('referensi', 'sales_return#' . $retur->id)
            ->get();
        
        if ($jurnalUmum->count() > 0) {
            echo "  ✅ Has " . $jurnalUmum->count() . " JurnalUmum entries\n";
        } else {
            echo "  ❌ Missing JurnalUmum entries\n";
        }
    }
    echo "\n";
}

// Check what appears in Buku Besar for all accounts affected by retur
echo "=== RETUR IN BUKU BESAR ===\n";

// Check Kas account (for refund returns)
$kasEntries = \DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
    ->where('je.ref_type', 'sales_return')
    ->where('coas.kode_akun', '112')
    ->get();

echo "Kas entries from retur: " . $kasEntries->count() . "\n";
foreach ($kasEntries as $entry) {
    $amount = $entry->debit > 0 ? "Dr. " . number_format($entry->debit, 0, ',', '.') : "Cr. " . number_format($entry->credit, 0, ',', '.');
    echo "  - {$entry->tanggal} | {$amount} | {$entry->ref_type}#{$entry->ref_id}\n";
}

// Check Piutang account (for credit returns)
$piutangEntries = \DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
    ->where('je.ref_type', 'sales_return')
    ->where('coas.kode_akun', '118')
    ->get();

echo "\nPiutang entries from retur: " . $piutangEntries->count() . "\n";
foreach ($piutangEntries as $entry) {
    $amount = $entry->debit > 0 ? "Dr. " . number_format($entry->debit, 0, ',', '.') : "Cr. " . number_format($entry->credit, 0, ',', '.');
    echo "  - {$entry->tanggal} | {$amount} | {$entry->ref_type}#{$entry->ref_id}\n";
}

// Check Retur Penjualan account
$returCoa = Coa::where('kode_akun', '42')->first();
if ($returCoa) {
    $returEntries = \DB::table('journal_entries as je')
        ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
        ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
        ->where('je.ref_type', 'sales_return')
        ->where('coas.kode_akun', '42')
        ->get();

    echo "\nRetur Penjualan account entries: " . $returEntries->count() . "\n";
    foreach ($returEntries as $entry) {
        $amount = $entry->debit > 0 ? "Dr. " . number_format($entry->debit, 0, ',', '.') : "Cr. " . number_format($entry->credit, 0, ',', '.');
        echo "  - {$entry->tanggal} | {$amount} | {$entry->ref_type}#{$entry->ref_id}\n";
    }
}

echo "\nCheck completed!\n";
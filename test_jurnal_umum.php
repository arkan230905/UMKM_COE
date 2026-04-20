<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TESTING JURNAL UMUM DATA ===\n\n";

// Test 1: Check penggajian entries
echo "TEST 1: Penggajian Entries\n";
$penggajianEntries = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
    ->where('je.ref_type', 'penggajian')
    ->select('je.id', 'je.tanggal', 'je.memo', 'jl.debit', 'jl.credit', 'coas.kode_akun', 'coas.nama_akun')
    ->get();

echo "Found " . count($penggajianEntries) . " penggajian journal lines\n";
foreach ($penggajianEntries as $entry) {
    echo "  - {$entry->tanggal} | {$entry->memo} | Debit: {$entry->debit} | Credit: {$entry->credit} | {$entry->kode_akun} {$entry->nama_akun}\n";
}

// Test 2: Check pembayaran beban entries
echo "\nTEST 2: Pembayaran Beban Entries\n";
$bebanEntries = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
    ->where('je.ref_type', 'pembayaran_beban')
    ->select('je.id', 'je.tanggal', 'je.memo', 'jl.debit', 'jl.credit', 'coas.kode_akun', 'coas.nama_akun')
    ->get();

echo "Found " . count($bebanEntries) . " pembayaran beban journal lines\n";
foreach ($bebanEntries as $entry) {
    echo "  - {$entry->tanggal} | {$entry->memo} | Debit: {$entry->debit} | Credit: {$entry->credit} | {$entry->kode_akun} {$entry->nama_akun}\n";
}

// Test 3: Verify debit = credit for each entry
echo "\nTEST 3: Verify Debit = Credit Balance\n";
$allEntries = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->whereIn('je.ref_type', ['penggajian', 'pembayaran_beban'])
    ->select('je.id', 'je.ref_type', DB::raw('SUM(jl.debit) as total_debit'), DB::raw('SUM(jl.credit) as total_credit'))
    ->groupBy('je.id', 'je.ref_type')
    ->get();

$balanced = true;
foreach ($allEntries as $entry) {
    $isBalanced = $entry->total_debit == $entry->total_credit;
    $status = $isBalanced ? '✅' : '❌';
    echo "$status Entry {$entry->id} ({$entry->ref_type}): Debit={$entry->total_debit}, Credit={$entry->total_credit}\n";
    if (!$isBalanced) $balanced = false;
}

echo "\n" . ($balanced ? "✅ ALL ENTRIES BALANCED!" : "❌ SOME ENTRIES NOT BALANCED!") . "\n";

echo "\n=== TEST COMPLETE ===\n";

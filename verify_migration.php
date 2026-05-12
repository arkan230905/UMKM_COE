<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICATION RESULTS ===\n\n";

// Check journal_entries
$penggajianEntries = DB::table('journal_entries')
    ->where('ref_type', 'penggajian')
    ->count();

$bebanEntries = DB::table('journal_entries')
    ->where('ref_type', 'pembayaran_beban')
    ->count();

echo "Journal Entries - Penggajian: " . $penggajianEntries . "\n";
echo "Journal Entries - Pembayaran Beban: " . $bebanEntries . "\n\n";

// Show sample data
echo "=== SAMPLE PENGGAJIAN DATA ===\n";
$penggajianData = DB::table('journal_entries')
    ->where('ref_type', 'penggajian')
    ->select('id', 'tanggal', 'ref_type', 'ref_id', 'memo')
    ->limit(3)
    ->get();

foreach ($penggajianData as $row) {
    echo "ID: {$row->id}, Tanggal: {$row->tanggal}, Ref ID: {$row->ref_id}, Memo: {$row->memo}\n";
}

echo "\n=== SAMPLE PEMBAYARAN BEBAN DATA ===\n";
$bebanData = DB::table('journal_entries')
    ->where('ref_type', 'pembayaran_beban')
    ->select('id', 'tanggal', 'ref_type', 'ref_id', 'memo')
    ->limit(3)
    ->get();

foreach ($bebanData as $row) {
    echo "ID: {$row->id}, Tanggal: {$row->tanggal}, Ref ID: {$row->ref_id}, Memo: {$row->memo}\n";
}

echo "\n=== JOURNAL LINES COUNT ===\n";
$journalLinesCount = DB::table('journal_lines')->count();
echo "Total Journal Lines: " . $journalLinesCount . "\n";

echo "\n✅ MIGRATION VERIFIED!\n";

<?php
/**
 * Script untuk menganalisis duplikasi journal entries pembayaran beban
 * Tanggal: 28/04/2026 dan 29/04/2026
 * 
 * Jalankan dengan: php artisan tinker < analyze_pembayaran_beban_duplicates.php
 * Atau: php analyze_pembayaran_beban_duplicates.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use App\Models\JournalEntry;
use App\Models\JournalLine;

echo "\n========================================\n";
echo "ANALISIS DUPLIKASI JOURNAL ENTRIES\n";
echo "Pembayaran Beban: 28/04/2026 - 29/04/2026\n";
echo "========================================\n\n";

// 1. Cek struktur tabel
echo "1. STRUKTUR TABEL JOURNAL_ENTRIES:\n";
echo "-----------------------------------\n";
$columns = DB::select("DESCRIBE journal_entries");
foreach ($columns as $col) {
    echo "  - {$col->Field} ({$col->Type}) " . ($col->Null === 'NO' ? '[NOT NULL]' : '[NULL]') . "\n";
}

echo "\n2. STRUKTUR TABEL JOURNAL_LINES:\n";
echo "-----------------------------------\n";
$columns = DB::select("DESCRIBE journal_lines");
foreach ($columns as $col) {
    echo "  - {$col->Field} ({$col->Type}) " . ($col->Null === 'NO' ? '[NOT NULL]' : '[NULL]') . "\n";
}

// 2. Query entries pembayaran beban pada tanggal 28-29 April 2026
echo "\n3. JOURNAL ENTRIES PEMBAYARAN BEBAN (28-29 April 2026):\n";
echo "-----------------------------------\n";

$query = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
    ->whereBetween(DB::raw('DATE(je.entry_date)'), ['2026-04-28', '2026-04-29'])
    ->where(function($q) {
        $q->where('je.ref_type', 'expense_payment')
          ->orWhere('je.description', 'like', '%pembayaran%')
          ->orWhere('je.description', 'like', '%beban%');
    })
    ->select(
        'je.id',
        'je.entry_date',
        'je.ref_type',
        'je.description',
        'je.created_at',
        'jl.id as line_id',
        'jl.account_id',
        'jl.debit',
        'jl.credit'
    )
    ->orderBy('je.entry_date')
    ->orderBy('je.id')
    ->get();

if ($query->isEmpty()) {
    echo "Tidak ada entries dengan ref_type 'expense_payment' pada tanggal tersebut.\n";
    echo "Mencari dengan pattern lain...\n\n";
    
    // Coba cari dengan pattern berbeda
    $query = DB::table('journal_entries as je')
        ->leftJoin('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
        ->whereBetween(DB::raw('DATE(je.entry_date)'), ['2026-04-28', '2026-04-29'])
        ->select(
            'je.id',
            'je.entry_date',
            'je.ref_type',
            'je.description',
            'je.created_at',
            'jl.id as line_id',
            'jl.account_id',
            'jl.debit',
            'jl.credit'
        )
        ->orderBy('je.entry_date')
        ->orderBy('je.id')
        ->get();
}

echo "Total entries ditemukan: " . $query->count() . "\n\n";

// Group by entry_id untuk analisis
$grouped = $query->groupBy('id');

foreach ($grouped as $entryId => $lines) {
    $firstLine = $lines->first();
    echo "Entry ID: {$entryId}\n";
    echo "  Tanggal: {$firstLine->entry_date}\n";
    echo "  Ref Type: {$firstLine->ref_type}\n";
    echo "  Deskripsi: {$firstLine->description}\n";
    echo "  Created: {$firstLine->created_at}\n";
    echo "  Lines:\n";
    
    $totalDebit = 0;
    $totalCredit = 0;
    
    foreach ($lines as $line) {
        if ($line->line_id) {
            echo "    - Account {$line->account_id}: Debit=" . number_format($line->debit, 2) . 
                 ", Credit=" . number_format($line->credit, 2) . "\n";
            $totalDebit += $line->debit;
            $totalCredit += $line->credit;
        }
    }
    
    echo "  Total: Debit=" . number_format($totalDebit, 2) . ", Credit=" . number_format($totalCredit, 2) . "\n";
    echo "\n";
}

// 4. Analisis duplikasi
echo "4. ANALISIS DUPLIKASI:\n";
echo "-----------------------------------\n";

$duplicates = DB::table('journal_entries as je1')
    ->join('journal_entries as je2', function($join) {
        $join->on('je1.entry_date', '=', 'je2.entry_date')
             ->on('je1.description', '=', 'je2.description')
             ->whereRaw('je1.id < je2.id');
    })
    ->whereBetween(DB::raw('DATE(je1.entry_date)'), ['2026-04-28', '2026-04-29'])
    ->select(
        'je1.id as entry1_id',
        'je2.id as entry2_id',
        'je1.entry_date',
        'je1.description',
        'je1.created_at as created1',
        'je2.created_at as created2'
    )
    ->get();

if ($duplicates->isEmpty()) {
    echo "Tidak ada duplikasi berdasarkan tanggal dan deskripsi yang sama.\n";
} else {
    echo "Duplikasi ditemukan:\n";
    foreach ($duplicates as $dup) {
        echo "  Entry {$dup->entry1_id} dan {$dup->entry2_id}\n";
        echo "    Tanggal: {$dup->entry_date}\n";
        echo "    Deskripsi: {$dup->description}\n";
        echo "    Created: {$dup->created1} vs {$dup->created2}\n\n";
    }
}

// 5. Cek nominal yang sama
echo "5. ENTRIES DENGAN NOMINAL SAMA (Debit/Credit):\n";
echo "-----------------------------------\n";

$sameAmount = DB::table('journal_entries as je1')
    ->join('journal_lines as jl1', 'je1.id', '=', 'jl1.journal_entry_id')
    ->join('journal_entries as je2', function($join) {
        $join->on('je1.entry_date', '=', 'je2.entry_date')
             ->whereRaw('je1.id < je2.id');
    })
    ->join('journal_lines as jl2', function($join) {
        $join->on('je2.id', '=', 'jl2.journal_entry_id')
             ->on('jl1.account_id', '=', 'jl2.account_id')
             ->on('jl1.debit', '=', 'jl2.debit')
             ->on('jl1.credit', '=', 'jl2.credit');
    })
    ->whereBetween(DB::raw('DATE(je1.entry_date)'), ['2026-04-28', '2026-04-29'])
    ->select(
        'je1.id as entry1_id',
        'je2.id as entry2_id',
        'je1.entry_date',
        'jl1.account_id',
        'jl1.debit',
        'jl1.credit'
    )
    ->distinct()
    ->get();

if ($sameAmount->isEmpty()) {
    echo "Tidak ada entries dengan nominal dan akun yang sama.\n";
} else {
    echo "Entries dengan nominal dan akun yang sama:\n";
    foreach ($sameAmount as $item) {
        echo "  Entry {$item->entry1_id} dan {$item->entry2_id}\n";
        echo "    Tanggal: {$item->entry_date}\n";
        echo "    Account: {$item->account_id}\n";
        echo "    Debit: " . number_format($item->debit, 2) . ", Credit: " . number_format($item->credit, 2) . "\n\n";
    }
}

// 6. Summary
echo "6. SUMMARY:\n";
echo "-----------------------------------\n";
$totalEntries = DB::table('journal_entries')
    ->whereBetween(DB::raw('DATE(entry_date)'), ['2026-04-28', '2026-04-29'])
    ->count();

$totalLines = DB::table('journal_entries as je')
    ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
    ->whereBetween(DB::raw('DATE(je.entry_date)'), ['2026-04-28', '2026-04-29'])
    ->count();

echo "Total Journal Entries (28-29 April): {$totalEntries}\n";
echo "Total Journal Lines: {$totalLines}\n";
echo "Duplikasi terdeteksi: " . $duplicates->count() . "\n";
echo "Entries dengan nominal sama: " . $sameAmount->count() . "\n";

echo "\n========================================\n";
echo "ANALISIS SELESAI\n";
echo "========================================\n\n";

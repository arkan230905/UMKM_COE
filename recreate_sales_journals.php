<?php
/**
 * Script to recreate journal entries for sales on April 21-23, 2026
 * Run with: php recreate_sales_journals.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Penjualan;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

echo "=== Recreating Sales Journal Entries for April 21-23, 2026 ===\n\n";

// Step 1: Get all sales between April 21-23
$sales = Penjualan::whereBetween('tanggal', ['2026-04-21', '2026-04-23'])
    ->with(['details.produk'])
    ->orderBy('tanggal')
    ->get();

echo "Found " . $sales->count() . " sales:\n";
foreach ($sales as $sale) {
    echo "  - {$sale->nomor_penjualan} on {$sale->tanggal} (ID: {$sale->id})\n";
}
echo "\n";

// Step 2: Delete existing journal entries for these sales
echo "Deleting existing journal entries...\n";
$deletedCount = JournalEntry::where('ref_type', 'sale')
    ->whereIn('ref_id', $sales->pluck('id'))
    ->delete();
echo "Deleted {$deletedCount} journal entries\n\n";

// Step 3: Get COA IDs we need
$coaKas = DB::table('coas')->where('id', 112)->first(); // Kas
$coaPendapatan = DB::table('coas')->where('id', 41)->first(); // PENDAPATAN
$coaHPP = DB::table('coas')->where('id', 185)->first(); // 1600 Harga Pokok Penjualan
$coaAyamCrispy = DB::table('coas')->where('id', 124)->first(); // 1161 Persediaan Ayam Crispy Macdi
$coaAyamGoreng = DB::table('coas')->where('id', 125)->first(); // 1162 Persediaan Ayam Goreng Bundo

if (!$coaKas || !$coaPendapatan) {
    die("ERROR: Required COA accounts not found (112 Kas or 41 Pendapatan)\n");
}

echo "COA Accounts:\n";
echo "  - Kas (112): " . ($coaKas ? "ID {$coaKas->id}" : "NOT FOUND") . "\n";
echo "  - Pendapatan (41): " . ($coaPendapatan ? "ID {$coaPendapatan->id}" : "NOT FOUND") . "\n";
echo "  - HPP (1600): " . ($coaHPP ? "ID {$coaHPP->id}" : "NOT FOUND") . "\n";
echo "  - Ayam Crispy (1161): " . ($coaAyamCrispy ? "ID {$coaAyamCrispy->id}" : "NOT FOUND") . "\n";
echo "  - Ayam Goreng (1162): " . ($coaAyamGoreng ? "ID {$coaAyamGoreng->id}" : "NOT FOUND") . "\n";
echo "\n";

// Step 4: Recreate journal entries for each sale
foreach ($sales as $sale) {
    echo "Processing {$sale->nomor_penjualan}...\n";
    
    // Create journal entry
    $journalEntry = JournalEntry::create([
        'tanggal' => $sale->tanggal,
        'memo' => 'Penjualan #' . $sale->nomor_penjualan,
        'ref_type' => 'sale',
        'ref_id' => $sale->id
    ]);
    
    // Line 1: Debit Kas
    JournalLine::create([
        'journal_entry_id' => $journalEntry->id,
        'coa_id' => $coaKas->id,
        'debit' => $sale->total,
        'credit' => 0,
        'memo' => 'Penerimaan tunai penjualan'
    ]);
    
    // Line 2: Credit Pendapatan
    JournalLine::create([
        'journal_entry_id' => $journalEntry->id,
        'coa_id' => $coaPendapatan->id,
        'debit' => 0,
        'credit' => $sale->total,
        'memo' => 'Pendapatan penjualan produk'
    ]);
    
    echo "  ✓ Created sales journal entry (ID: {$journalEntry->id})\n";
    
    // Calculate HPP and create HPP journal entry
    $totalHPP = 0;
    $produkId = null;
    
    foreach ($sale->details as $detail) {
        if ($detail->produk) {
            $produkId = $detail->produk_id;
            // Get HPP from product's harga_bom
            $hppPerUnit = $detail->produk->harga_bom ?? 0;
            $totalHPP += $hppPerUnit * $detail->jumlah;
        }
    }
    
    if ($totalHPP > 0 && $coaHPP) {
        // Determine which finished goods account to use
        $coaPersediaan = null;
        if ($produkId == 1 && $coaAyamCrispy) {
            $coaPersediaan = $coaAyamCrispy;
        } elseif ($produkId == 2 && $coaAyamGoreng) {
            $coaPersediaan = $coaAyamGoreng;
        }
        
        if ($coaPersediaan) {
            // Create HPP journal entry
            $hppEntry = JournalEntry::create([
                'tanggal' => $sale->tanggal,
                'memo' => 'HPP Penjualan ' . $sale->nomor_penjualan,
                'ref_type' => 'sale',
                'ref_id' => $sale->id
            ]);
            
            // Line 1: Debit HPP (Expense)
            JournalLine::create([
                'journal_entry_id' => $hppEntry->id,
                'coa_id' => $coaHPP->id,
                'debit' => $totalHPP,
                'credit' => 0,
                'memo' => 'Harga Pokok Penjualan'
            ]);
            
            // Line 2: Credit Persediaan Barang Jadi
            JournalLine::create([
                'journal_entry_id' => $hppEntry->id,
                'coa_id' => $coaPersediaan->id,
                'debit' => 0,
                'credit' => $totalHPP,
                'memo' => 'Pengurangan persediaan barang jadi'
            ]);
            
            echo "  ✓ Created HPP journal entry (ID: {$hppEntry->id}, HPP: Rp " . number_format($totalHPP, 0, ',', '.') . ")\n";
        } else {
            echo "  ⚠ Skipped HPP entry - persediaan COA not found for product ID {$produkId}\n";
        }
    } else {
        echo "  ⚠ Skipped HPP entry - HPP is 0 or COA 1600 not found\n";
    }
    
    echo "\n";
}

echo "=== Done ===\n";

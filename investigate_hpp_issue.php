<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== INVESTIGATING HPP JOURNAL ENTRIES ===\n\n";

// 1. Find all journal_lines using account 116 (parent account)
echo "1. Journal lines using account 116 (Persediaan Barang Jadi - Parent):\n";
echo str_repeat("-", 80) . "\n";

$lines116 = DB::table('journal_lines as jl')
    ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
    ->join('coas as c', 'c.id', '=', 'jl.coa_id')
    ->where('c.kode_akun', '116')
    ->whereBetween('je.tanggal', ['2026-04-21', '2026-04-23'])
    ->select('jl.id as line_id', 'je.id as entry_id', 'je.tanggal', 'je.memo', 'jl.debit', 'jl.credit', 'c.kode_akun', 'c.nama_akun')
    ->orderBy('je.tanggal')
    ->get();

foreach ($lines116 as $line) {
    echo sprintf(
        "Line ID: %d | Entry ID: %d | Date: %s | Memo: %s\n  Account: %s - %s | Debit: %s | Credit: %s\n\n",
        $line->line_id,
        $line->entry_id,
        $line->tanggal,
        $line->memo,
        $line->kode_akun,
        $line->nama_akun,
        number_format($line->debit, 2),
        number_format($line->credit, 2)
    );
}

// 2. Find all penjualan records in this date range
echo "\n2. Penjualan records (21-23 April):\n";
echo str_repeat("-", 80) . "\n";

$penjualans = DB::table('penjualans as p')
    ->leftJoin('detail_penjualans as dp', 'dp.penjualan_id', '=', 'p.id')
    ->leftJoin('produks as pr', 'pr.id', '=', 'dp.produk_id')
    ->leftJoin('coas as c', 'c.id', '=', 'pr.coa_persediaan_id')
    ->whereBetween('p.tanggal', ['2026-04-21', '2026-04-23'])
    ->select(
        'p.id as penjualan_id',
        'p.nomor_penjualan',
        'p.tanggal',
        'dp.produk_id',
        'pr.nama_produk',
        'pr.coa_persediaan_id',
        'c.kode_akun as coa_persediaan_kode',
        'c.nama_akun as coa_persediaan_nama'
    )
    ->orderBy('p.tanggal')
    ->get();

foreach ($penjualans as $penjualan) {
    echo sprintf(
        "Penjualan ID: %d | No: %s | Date: %s\n  Product ID: %s | Product: %s\n  COA Persediaan ID: %s | COA: %s - %s\n\n",
        $penjualan->penjualan_id,
        $penjualan->nomor_penjualan,
        $penjualan->tanggal,
        $penjualan->produk_id ?? 'NULL',
        $penjualan->nama_produk ?? 'NULL',
        $penjualan->coa_persediaan_id ?? 'NULL',
        $penjualan->coa_persediaan_kode ?? 'NULL',
        $penjualan->coa_persediaan_nama ?? 'NULL'
    );
}

// 3. Find all journal entries for penjualan
echo "\n3. All journal entries with ref_type = 'penjualan' (21-23 April):\n";
echo str_repeat("-", 80) . "\n";

$penjualanEntries = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->leftJoin('coas as c', 'c.id', '=', 'jl.coa_id')
    ->where('je.ref_type', 'penjualan')
    ->whereBetween('je.tanggal', ['2026-04-21', '2026-04-23'])
    ->select('je.id as entry_id', 'je.tanggal', 'je.memo', 'je.ref_id', 'jl.id as line_id', 'c.kode_akun', 'c.nama_akun', 'jl.debit', 'jl.credit')
    ->orderBy('je.tanggal', 'asc')
    ->orderBy('je.id', 'asc')
    ->orderBy('jl.id', 'asc')
    ->get();

$currentEntryId = null;
foreach ($penjualanEntries as $entry) {
    if ($currentEntryId !== $entry->entry_id) {
        if ($currentEntryId !== null) {
            echo "\n";
        }
        echo sprintf(
            "Entry ID: %d | Date: %s | Ref ID: %s\n  Memo: %s\n",
            $entry->entry_id,
            $entry->tanggal,
            $entry->ref_id,
            $entry->memo
        );
        $currentEntryId = $entry->entry_id;
    }
    echo sprintf(
        "  Line ID: %d | %s - %s | Debit: %s | Credit: %s\n",
        $entry->line_id,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 2),
        number_format($entry->credit, 2)
    );
}

// 4. Check child accounts 1161 and 1162
echo "\n\n4. Child accounts for Persediaan Barang Jadi:\n";
echo str_repeat("-", 80) . "\n";

$childAccounts = DB::table('coas')
    ->whereIn('kode_akun', ['1161', '1162'])
    ->get();

foreach ($childAccounts as $account) {
    echo sprintf(
        "COA ID: %d | Code: %s | Name: %s\n",
        $account->id,
        $account->kode_akun,
        $account->nama_akun
    );
}

echo "\n=== INVESTIGATION COMPLETE ===\n";

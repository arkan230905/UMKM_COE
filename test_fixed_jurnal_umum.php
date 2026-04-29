<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Fixed Jurnal Umum Query...\n\n";

// Test the updated controller query
echo "Updated Controller Query:\n";
echo "========================\n";

$jurnalUmumQuery = \DB::table('jurnal_umum as ju')
    ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
    ->select([
        'ju.id',
        'ju.tanggal',
        'ju.coa_id',
        'ju.debit',
        'ju.kredit',
        'ju.keterangan',
        'ju.tipe_referensi',
        'ju.referensi',
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun',
        'ju.created_at',
        \DB::raw("'ju_' as ref_type"),
        \DB::raw('NULL as ref_id')
    ])
    ->where(function($q) {
        $q->where('ju.debit', '>', 0)
          ->orWhere('ju.kredit', '>', 0);
    })
    ->whereIn('ju.tipe_referensi', [
        'penyusutan', 'adjustment', 'manual', 'pembelian' // Include purchase journals
    ])
    ->orderBy('ju.created_at','asc')
    ->orderBy('ju.id','asc');

echo "Query now includes 'pembelian' in the whereIn clause\n\n";

$results = $jurnalUmumQuery->get();

echo "Found {$results->count()} total entries (including purchase)\n\n";

if ($results->count() > 0) {
    echo "Entries by type:\n";
    $typeCounts = $results->groupBy('tipe_referensi');
    foreach ($typeCounts as $type => $entries) {
        echo "- {$type}: {$entries->count()} entries\n";
    }
    
    echo "\nSample purchase entries:\n";
    echo "======================\n";
    
    $purchaseEntries = $results->where('tipe_referensi', 'pembelian');
    foreach ($purchaseEntries->take(3) as $result) {
        echo "ID: {$result->id}\n";
        echo "Tanggal: {$result->tanggal}\n";
        echo "COA: {$result->kode_akun} - {$result->nama_akun}\n";
        echo "Debit: {$result->debit}\n";
        echo "Kredit: {$result->kredit}\n";
        echo "Keterangan: {$result->keterangan}\n";
        echo "Referensi: {$result->referensi}\n";
        echo "Tipe: {$result->tipe_referensi}\n";
        echo "Ref Type: {$result->ref_type}\n";
        echo "--------------------------------\n";
    }
    
    // Test grouping logic
    echo "\nTesting grouping logic:\n";
    echo "======================\n";
    
    $jurnalUmumGrouped = $results->groupBy(function($item) {
        return $item->tanggal . '|' . $item->keterangan;
    });
    
    echo "Grouped into {$jurnalUmumGrouped->count()} journal entries\n\n";
    
    foreach ($jurnalUmumGrouped->take(2) as $key => $group) {
        $firstItem = $group->first();
        echo "Group: {$key}\n";
        echo "Lines: {$group->count()}\n";
        echo "First line COA: {$firstItem->kode_akun} - {$firstItem->nama_akun}\n";
        echo "Total debit: " . $group->sum('debit') . "\n";
        echo "Total kredit: " . $group->sum('kredit') . "\n";
        echo "----------------\n";
    }
} else {
    echo "No entries found!\n";
}

echo "\nFixed jurnal umum test completed!\n";

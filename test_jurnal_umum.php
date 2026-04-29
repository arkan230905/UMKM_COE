<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Jurnal Umum for Purchase Journals...\n\n";

// Test the exact same query as the controller
echo "Testing Controller Query Logic:\n";
echo "=============================\n";

// Simulate the controller query
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
        'ju.created_at'
    ])
    ->orderBy('ju.created_at','asc')
    ->orderBy('ju.id','asc');

// Apply purchase filter
$jurnalUmumQuery->where('ju.tipe_referensi', 'pembelian');

echo "Query for purchase journals:\n";
echo "SELECT ju.id, ju.tanggal, ju.coa_id, ju.debit, ju.kredit, ju.keterangan, ju.tipe_referensi, ju.referensi, coas.kode_akun, coas.nama_akun, ju.created_at\n";
echo "FROM jurnal_umum ju LEFT JOIN coas ON coas.id = ju.coa_id\n";
echo "WHERE ju.tipe_referensi = 'pembelian'\n";
echo "ORDER BY ju.created_at ASC, ju.id ASC\n\n";

$results = $jurnalUmumQuery->get();

echo "Found {$results->count()} purchase journal entries\n\n";

if ($results->count() > 0) {
    echo "Purchase Journal Entries:\n";
    echo "========================\n";
    
    foreach ($results as $result) {
        echo "ID: {$result->id}\n";
        echo "Tanggal: {$result->tanggal}\n";
        echo "COA: {$result->kode_akun} - {$result->nama_akun}\n";
        echo "Debit: {$result->debit}\n";
        echo "Kredit: {$result->kredit}\n";
        echo "Keterangan: {$result->keterangan}\n";
        echo "Referensi: {$result->referensi}\n";
        echo "Tipe: {$result->tipe_referensi}\n";
        echo "Created: {$result->created_at}\n";
        echo "--------------------------------\n";
    }
} else {
    echo "No purchase journal entries found!\n\n";
    
    // Check if there are any jurnal_umum entries at all
    $allEntries = \DB::table('jurnal_umum')->get();
    echo "Total jurnal_umum entries: {$allEntries->count()}\n";
    
    if ($allEntries->count() > 0) {
        echo "\nAll tipe_referensi values:\n";
        $types = $allEntries->pluck('tipe_referensi')->unique();
        foreach ($types as $type) {
            $count = $allEntries->where('tipe_referensi', $type)->count();
            echo "- {$type}: {$count} entries\n";
        }
    }
}

// Test production journals for comparison
echo "\n\nTesting Production Journals (for comparison):\n";
echo "==========================================\n";

$productionQuery = \DB::table('jurnal_umum as ju')
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
        'ju.created_at'
    ])
    ->where('ju.tipe_referensi', 'like', 'produksi%')
    ->orderBy('ju.created_at','asc')
    ->orderBy('ju.id','asc');

$productionResults = $productionQuery->get();

echo "Found {$productionResults->count()} production journal entries\n";

if ($productionResults->count() > 0) {
    echo "Production types found:\n";
    $prodTypes = $productionResults->pluck('tipe_referensi')->unique();
    foreach ($prodTypes as $type) {
        $count = $productionResults->where('tipe_referensi', $type)->count();
        echo "- {$type}: {$count} entries\n";
    }
}

echo "\nJurnal Umum test completed!\n";

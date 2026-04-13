<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Checking COA codes in database...\n\n";

// Check all COA codes
$coas = \App\Models\Coa::orderBy('kode_akun')->get(['kode_akun', 'nama_akun', 'tipe_akun']);

echo "Available COA codes:\n";
foreach ($coas as $coa) {
    echo sprintf("%-6s %-40s %-10s\n", $coa->kode_akun, $coa->nama_akun, $coa->tipe_akun);
}

echo "\n\nSearching for specific COA codes needed for purchase journal...\n";

// Check for persediaan bahan baku
$persediaanBB = \App\Models\Coa::where('tipe_akun', 'Asset')
    ->where(function($query) {
        $query->where('nama_akun', 'like', '%persediaan%')
              ->orWhere('nama_akun', 'like', '%stok%')
              ->orWhere('nama_akun', 'like', '%inventory%');
    })
    ->get();

echo "\nCOA for Persediaan (Inventory):\n";
foreach ($persediaanBB as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})\n";
}

// Check for PPN masukan
$ppnMasukan = \App\Models\Coa::where('tipe_akun', 'Asset')
    ->where(function($query) {
        $query->where('nama_akun', 'like', '%ppn%masukan%')
              ->orWhere('nama_akun', 'like', '%ppn%')
              ->orWhere('kode_akun', '1130');
    })
    ->get();

echo "\nCOA for PPN Masukan:\n";
foreach ($ppnMasukan as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})\n";
}

// Check for hutang usaha
$hutangUsaha = \App\Models\Coa::where('tipe_akun', 'Liability')
    ->where(function($query) {
        $query->where('nama_akun', 'like', '%hutang%usaha%')
              ->orWhere('nama_akun', 'like', '%hutang%')
              ->orWhere('kode_akun', '2101');
    })
    ->get();

echo "\nCOA for Hutang Usaha:\n";
foreach ($hutangUsaha as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})\n";
}

// Check the specific bank COA used in purchase
$bankCoa = \App\Models\Coa::find(87);
echo "\nBank COA used in purchase (ID 87):\n";
if ($bankCoa) {
    echo "  {$bankCoa->kode_akun} - {$bankCoa->nama_akun} ({$bankCoa->tipe_akun})\n";
} else {
    echo "  NOT FOUND\n";
}

echo "\nDone.\n";

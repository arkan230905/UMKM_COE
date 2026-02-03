<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG BOP DATA STEP BY STEP ===\n";

// Step 1: Check bom_job_btkl data
echo "1. BOM Job BTKL Data:\n";
$bomJobBtkl = \Illuminate\Support\Facades\DB::table('bom_job_btkl')->get();
echo "   Records: " . $bomJobBtkl->count() . "\n";
foreach ($bomJobBtkl as $item) {
    echo "   - Proses ID: " . $item->proses_produksi_id . "\n";
}

// Step 2: Get proses IDs
$prosesIdsWithBTKL = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
    ->distinct()
    ->pluck('proses_produksi_id')
    ->toArray();
echo "\n2. Proses IDs with BTKL: " . implode(', ', $prosesIdsWithBTKL) . "\n";

// Step 3: Get proses data
echo "\n3. Proses Produksi Data:\n";
$prosesProduksis = \App\Models\ProsesProduksi::whereIn('id', $prosesIdsWithBTKL)
    ->with('bopProses')
    ->orderBy('kode_proses')
    ->get();
echo "   Records: " . $prosesProduksis->count() . "\n";
foreach ($prosesProduksis as $proses) {
    echo "   - " . $proses->kode_proses . " - " . $proses->nama_proses . "\n";
    echo "     BOP Proses: " . ($proses->bopProses ? 'YES' : 'NO') . "\n";
}

// Step 4: Check COA data
echo "\n4. COA Akun Beban Data:\n";
$akunBeban = \App\Models\Coa::where('kode_akun', 'LIKE', '5%')
    ->where('is_akun_header', false)
    ->orderBy('kode_akun')
    ->get();
echo "   Records: " . $akunBeban->count() . "\n";
foreach ($akunBeban as $akun) {
    echo "   - " . $akun->kode_akun . " - " . $akun->nama_akun . " (Header: " . ($akun->is_akun_header ? 'YES' : 'NO') . ")\n";
}

// Step 5: Check BopLainnya table
echo "\n5. BOP Lainnya Table:\n";
$bopLainnyaTable = \Illuminate\Support\Facades\DB::table('bop_lainnyas')->get();
echo "   Records: " . $bopLainnyaTable->count() . "\n";
foreach ($bopLainnyaTable as $bop) {
    echo "   - " . $bop->kode_akun . " - " . $bop->nama_akun . " (Budget: " . $bop->budget . ")\n";
}

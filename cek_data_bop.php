<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DATA AKUN BEBAN (COA) ===\n";
$akunBeban = \App\Models\Coa::where('kode_akun', 'LIKE', '5%')->where('is_akun_header', false)->get();
echo "Jumlah akun beban: " . $akunBeban->count() . "\n";
foreach ($akunBeban as $akun) {
    echo $akun->kode_akun . " - " . $akun->nama_akun . "\n";
}

echo "\n=== CEK DATA PROSES BTKL ===\n";
$prosesIdsWithBTKL = \Illuminate\Support\Facades\DB::table('bom_job_btkl')->distinct()->pluck('proses_produksi_id')->toArray();
echo "Jumlah proses dengan BTKL: " . count($prosesIdsWithBTKL) . "\n";

$prosesDenganBTKL = \App\Models\ProsesProduksi::whereIn('id', $prosesIdsWithBTKL)->get();
foreach ($prosesDenganBTKL as $proses) {
    echo $proses->kode_proses . " - " . $proses->nama_proses . "\n";
}

echo "\n=== CEK BOM JOB BTKL DETAIL ===\n";
$bomJobBtkl = \Illuminate\Support\Facades\DB::table('bom_job_btkl')->get();
echo "Jumlah records di bom_job_btkl: " . $bomJobBtkl->count() . "\n";
foreach ($bomJobBtkl as $detail) {
    echo "Proses ID: " . $detail->proses_produksi_id . ", Durasi: " . $detail->durasi_jam . "\n";
}

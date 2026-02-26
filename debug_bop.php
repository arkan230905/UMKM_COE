<?php
$produk = App\Models\Produk::find(3);
$bomJobCosting = App\Models\BomJobCosting::where('produk_id', 3)->first();
echo 'Produk: ' . $produk->nama_produk . PHP_EOL;
echo 'BomJobCosting ID: ' . ($bomJobCosting ? $bomJobCosting->id : 'null') . PHP_EOL;
echo 'Total BOP dari BomJobCosting: ' . ($bomJobCosting ? $bomJobCosting->total_bop : 'null') . PHP_EOL;

// Cek BOP data
$bopData = Illuminate\Support\Facades\DB::table('bom_job_bop')
    ->where('bom_job_costing_id', $bomJobCosting->id ?? 0)
    ->get();
    
echo 'Jumlah BOP records: ' . $bopData->count() . PHP_EOL;
foreach($bopData as $bop) {
    echo '- ' . $bop->nama_bop . ': ' . $bop->tarif . PHP_EOL;
}

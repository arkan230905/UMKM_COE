<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$proses = DB::table('produksi_proses')->where('produksi_id', 3)->get();
foreach($proses as $p) {
    echo "ID: " . $p->id . PHP_EOL;
    echo "  Nama: " . $p->nama_proses . PHP_EOL;
    echo "  Biaya BTKL: " . $p->biaya_btkl . PHP_EOL;
    echo "  Biaya BOP: " . $p->biaya_bop . PHP_EOL;
    echo "  Total Biaya Proses: " . $p->total_biaya_proses . PHP_EOL;
    echo PHP_EOL;
}

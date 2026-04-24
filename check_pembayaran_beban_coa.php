<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Cek Pembayaran Beban yang Menggunakan COA NULL ===\n\n";

$data = DB::table('pembayaran_beban')
    ->whereIn('akun_kas_id', [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20])
    ->get(['id', 'akun_kas_id', 'akun_beban_id']);

echo "Total: " . $data->count() . " record\n\n";

foreach ($data as $item) {
    echo "ID: {$item->id} | akun_kas_id: {$item->akun_kas_id} | akun_beban_id: {$item->akun_beban_id}\n";
}

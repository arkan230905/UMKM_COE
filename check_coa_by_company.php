<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Cek COA per Company ===\n\n";

// Cek total per company
$perCompany = DB::table('coas')
    ->select('company_id', DB::raw('COUNT(*) as jumlah'))
    ->groupBy('company_id')
    ->get();

echo "Total COA per Company:\n";
foreach ($perCompany as $item) {
    $companyId = $item->company_id ?? 'NULL';
    echo "  Company ID $companyId: {$item->jumlah} akun\n";
}

echo "\n";

// Cek duplikat kode_akun dalam company yang sama
$duplicatesInCompany = DB::table('coas')
    ->select('company_id', 'kode_akun', DB::raw('COUNT(*) as jumlah'))
    ->groupBy('company_id', 'kode_akun')
    ->having('jumlah', '>', 1)
    ->get();

if ($duplicatesInCompany->count() > 0) {
    echo "Duplikat dalam company yang sama:\n";
    foreach ($duplicatesInCompany as $dup) {
        $companyId = $dup->company_id ?? 'NULL';
        echo "  Company $companyId - Kode {$dup->kode_akun}: {$dup->jumlah} duplikat\n";
    }
} else {
    echo "✓ Tidak ada duplikat dalam company yang sama\n";
}

echo "\n";

// Tampilkan beberapa contoh data
echo "Contoh data COA:\n";
$samples = DB::table('coas')
    ->orderBy('company_id')
    ->orderBy('kode_akun')
    ->limit(10)
    ->get(['id', 'company_id', 'kode_akun', 'nama_akun']);

foreach ($samples as $s) {
    $companyId = $s->company_id ?? 'NULL';
    echo "  ID:{$s->id} | Company:$companyId | {$s->kode_akun} - {$s->nama_akun}\n";
}

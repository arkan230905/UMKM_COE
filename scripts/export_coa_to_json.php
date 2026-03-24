<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = [
    'kode_akun',
    'nama_akun',
    'kategori_akun',
    'tipe_akun',
    'kode_induk',
    'saldo_normal',
    'keterangan',
    'is_akun_header',
    'saldo_awal',
    'tanggal_saldo_awal',
    'posted_saldo_awal',
];

$rows = DB::table('coas')
    ->orderBy('kode_akun')
    ->get($cols)
    ->map(function ($r) {
        return [
            'kode_akun' => (string) ($r->kode_akun ?? ''),
            'nama_akun' => $r->nama_akun,
            'kategori_akun' => $r->kategori_akun,
            'tipe_akun' => $r->tipe_akun,
            'kode_induk' => $r->kode_induk,
            'saldo_normal' => $r->saldo_normal,
            'keterangan' => $r->keterangan,
            'is_akun_header' => (int) ($r->is_akun_header ?? 0),
            'saldo_awal' => $r->saldo_awal,
            'tanggal_saldo_awal' => $r->tanggal_saldo_awal,
            'posted_saldo_awal' => (int) ($r->posted_saldo_awal ?? 0),
        ];
    })
    ->values()
    ->toArray();

$targetPath = __DIR__ . '/../database/seeders/coa_data.json';

if (!is_dir(dirname($targetPath))) {
    mkdir(dirname($targetPath), 0777, true);
}

file_put_contents(
    $targetPath,
    json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "Exported " . count($rows) . " rows to {$targetPath}\n";

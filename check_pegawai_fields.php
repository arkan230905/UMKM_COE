<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Struktur tabel pegawais:\n\n";
$cols = DB::select('DESCRIBE pegawais');
foreach($cols as $c) {
    echo "- {$c->Field} ({$c->Type})\n";
}

echo "\n\nSample pegawai data:\n";
$pegawai = DB::table('pegawais')->first();
if ($pegawai) {
    foreach((array)$pegawai as $key => $val) {
        echo "  {$key}: {$val}\n";
    }
}

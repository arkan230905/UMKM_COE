<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Struktur Tabel ASETS ===\n\n";

$columns = DB::select("PRAGMA table_info('asets')");

foreach ($columns as $col) {
    echo sprintf("%-3s %-30s %-15s %s\n", 
        $col->cid, 
        $col->name, 
        $col->type,
        $col->notnull ? 'NOT NULL' : 'NULL'
    );
}

echo "\n=== Total kolom: " . count($columns) . " ===\n";

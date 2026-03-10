<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Structure of bahan_pendukungs table:\n";
$columns = \Schema::getColumnListing('bahan_pendukungs');
foreach ($columns as $column) {
    echo "- $column\n";
}

echo "\nSample data from bahan_pendukungs:\n";
$data = \DB::table('bahan_pendukungs')->limit(1)->get();
foreach ($data as $item) {
    foreach ($item as $key => $value) {
        echo "$key: $value\n";
    }
}

echo "\nStructure of bahan_pendukung table:\n";
$columns = \Schema::getColumnListing('bahan_pendukung');
foreach ($columns as $column) {
    echo "- $column\n";
}

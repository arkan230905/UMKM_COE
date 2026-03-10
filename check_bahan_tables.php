<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking tables:\n";
$tables = \DB::select('SHOW TABLES LIKE "%bahan%"');
foreach ($tables as $table) {
    foreach ($table as $key => $value) {
        echo "$value\n";
    }
}

echo "\nChecking bahan_pendukung table:\n";
if (\Schema::hasTable('bahan_pendukung')) {
    $count = \DB::table('bahan_pendukung')->count();
    echo "bahan_pendukung: $count records\n";
    $data = \DB::table('bahan_pendukung')->limit(3)->get();
    foreach ($data as $item) {
        echo "- {$item->nama}\n";
    }
}

echo "\nChecking bahan_pendukungs table:\n";
if (\Schema::hasTable('bahan_pendukungs')) {
    $count = \DB::table('bahan_pendukungs')->count();
    echo "bahan_pendukungs: $count records\n";
    $data = \DB::table('bahan_pendukungs')->limit(3)->get();
    foreach ($data as $item) {
        echo "- {$item->nama_bahan}\n";
    }
}

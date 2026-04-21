<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Table Structure ===" . PHP_EOL;

// Check bahan_bakus structure
echo PHP_EOL . "Bahan Bakus Table Structure:" . PHP_EOL;
$bahanBakuColumns = DB::select('DESCRIBE bahan_bakus');
foreach ($bahanBakuColumns as $column) {
    echo "- " . $column->Field . " (" . $column->Type . ")" . PHP_EOL;
}

// Check bahan_pendukungs structure
echo PHP_EOL . "Bahan Pendukungs Table Structure:" . PHP_EOL;
$bahanPendukungColumns = DB::select('DESCRIBE bahan_pendukungs');
foreach ($bahanPendukungColumns as $column) {
    echo "- " . $column->Field . " (" . $column->Type . ")" . PHP_EOL;
}

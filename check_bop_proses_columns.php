<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = DB::select("SHOW COLUMNS FROM bop_proses");
echo "Columns in bop_proses table:\n";
foreach ($columns as $col) {
    echo "- {$col->Field} ({$col->Type})\n";
}

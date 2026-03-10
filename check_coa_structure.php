<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking COA table structure:\n";
echo "============================\n";

$columns = \Schema::getColumnListing('coas');
foreach ($columns as $column) {
    echo "- $column\n";
}

echo "\nSample existing COA data:\n";
echo "==========================\n";
$sampleData = \DB::table('coas')->limit(3)->get();
foreach ($sampleData as $coa) {
    foreach ($coa as $key => $value) {
        echo "$key: $value\n";
    }
    echo "---\n";
}

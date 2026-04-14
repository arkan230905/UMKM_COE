<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING BOM_JOB_BTKL TABLE STRUCTURE ===\n";

$columns = DB::select("DESCRIBE bom_job_btkl");
echo "Columns in bom_job_btkl:\n";
foreach($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

echo "\n=== SAMPLE DATA ===\n";
$data = DB::table('bom_job_btkl')->limit(3)->get();
echo "Sample records: " . $data->count() . "\n";
foreach($data as $record) {
    echo "ID: {$record->id}\n";
    foreach((array)$record as $key => $value) {
        echo "  {$key}: " . ($value ?? 'NULL') . "\n";
    }
    echo "---\n";
}
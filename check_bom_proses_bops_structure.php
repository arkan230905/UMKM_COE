<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING bom_proses_bops TABLE STRUCTURE ===\n\n";

$columns = \DB::select('DESCRIBE bom_proses_bops');

foreach ($columns as $column) {
    echo "Field: {$column->Field} | Type: {$column->Type} | Null: {$column->Null} | Key: {$column->Key} | Default: {$column->Default}\n";
}

echo "\n=== SAMPLE DATA ===\n\n";
$samples = \DB::table('bom_proses_bops')->take(3)->get();
foreach ($samples as $sample) {
    echo "ID: {$sample->id} | bom_proses_id: {$sample->bom_proses_id} | komponen_bop_id: " . ($sample->komponen_bop_id ?? 'NULL') . " | kuantitas: {$sample->kuantitas} | tarif: {$sample->tarif} | total_biaya: {$sample->total_biaya}\n";
}

echo "\n=== DONE ===\n";

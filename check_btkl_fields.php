<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECK BTKL FIELDS\n";
echo "================\n\n";

// Check database columns vs model fillable
$dbColumns = \Illuminate\Support\Facades\Schema::getColumnListing('btkls');
$modelFillable = ['kode_proses', 'jabatan_id', 'tarif_per_jam', 'satuan', 'kapasitas_per_jam', 'deskripsi_proses', 'is_active'];

echo "Database columns: " . implode(', ', $dbColumns) . "\n";
echo "Model fillable: " . implode(', ', $modelFillable) . "\n";

$hiddenFields = array_diff($dbColumns, $modelFillable);
if (!empty($hiddenFields)) {
    echo "Hidden fields: " . implode(', ', $hiddenFields) . "\n";
} else {
    echo "No hidden fields\n";
}

echo "\nChecking for biaya_per_produk field:\n";
if (in_array('biaya_per_produk', $dbColumns)) {
    echo "Found biaya_per_produk field!\n";
} else {
    echo "No biaya_per_produk field found\n";
}

echo "\nChecking actual BTKL data:\n";
$btkl = \App\Models\Btkl::first();
if ($btkl) {
    foreach($dbColumns as $column) {
        $value = $btkl->$column;
        if (is_numeric($value) && $value > 100000) {
            echo "LARGE: " . $column . " = " . number_format($value, 0, ',', '.') . "\n";
        }
    }
}

echo "\n✅ Check completed!\n";

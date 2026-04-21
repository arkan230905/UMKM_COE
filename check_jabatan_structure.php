<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Jabatan Table Structure ===" . PHP_EOL;

// Get table structure
$columns = DB::select("DESCRIBE jabatans");
echo "Columns in jabatans table:" . PHP_EOL;
foreach ($columns as $column) {
    echo "- " . $column->Field . " (" . $column->Type . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Search for Gudang ===" . PHP_EOL;

// Try different column names for jabatan name
$possibleColumns = ['nama_jabatan', 'nama', 'jabatan'];
$found = false;

foreach ($possibleColumns as $column) {
    try {
        $jabatan = DB::table('jabatans')
            ->where($column, 'like', '%gudang%')
            ->first();
        
        if ($jabatan) {
            echo PHP_EOL . "✅ Found in column '" . $column . "':" . PHP_EOL;
            echo "ID: " . $jabatan->id . PHP_EOL;
            echo "Nama: " . $jabatan->$column . PHP_EOL;
            echo "Kategori: " . ($jabatan->kategori ?? 'N/A') . PHP_EOL;
            $found = true;
            break;
        }
    } catch (\Exception $e) {
        echo "❌ Error with column '" . $column . "': " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== All Jabatan Data ===" . PHP_EOL;

// Show all jabatan data
$allJabatans = DB::table('jabatans')->limit(10)->get();
foreach ($allJabatans as $jabatan) {
    echo "ID: " . $jabatan->id . " | Nama: " . ($jabatan->nama ?? $jabatan->nama_jabatan ?? 'N/A') . " | Kategori: " . ($jabatan->kategori ?? 'N/A') . PHP_EOL;
}

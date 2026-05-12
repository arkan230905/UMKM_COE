<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Mencari Lokasi Data BTKL di Database ===\n\n";

try {
    // 1. Cek semua tabel yang ada
    $tables = DB::select('SHOW TABLES');
    echo "Total tabel di database: " . count($tables) . "\n\n";

    // 2. Cari tabel yang mungkin mengandung data BTKL
    $possibleTables = [];
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        if (stripos($tableName, 'btkl') !== false || 
            stripos($tableName, 'proses') !== false || 
            stripos($tableName, 'tenaga') !== false ||
            stripos($tableName, 'kerja') !== false) {
            $possibleTables[] = $tableName;
        }
    }

    echo "Tabel yang mungkin mengandung data BTKL:\n";
    foreach ($possibleTables as $table) {
        echo "- $table\n";
    }
    echo "\n";

    // 3. Periksa setiap tabel yang mungkin
    foreach ($possibleTables as $tableName) {
        echo "=== Memeriksa tabel: $tableName ===\n";
        
        if (Schema::hasTable($tableName)) {
            $columns = Schema::getColumnListing($tableName);
            echo "Kolom: " . implode(', ', $columns) . "\n";
            
            $count = DB::table($tableName)->count();
            echo "Jumlah records: $count\n";
            
            if ($count > 0) {
                // Tampilkan beberapa sample data
                $sample = DB::table($tableName)->limit(2)->get();
                echo "Sample data:\n";
                foreach ($sample as $record) {
                    echo "  ID: " . ($record->id ?? 'N/A') . "\n";
                    if (isset($record->nama_btkl)) echo "  Nama BTKL: {$record->nama_btkl}\n";
                    if (isset($record->nama_proses)) echo "  Nama Proses: {$record->nama_proses}\n";
                    if (isset($record->tarif_btkl)) echo "  Tarif BTKL: {$record->tarif_btkl}\n";
                    if (isset($record->tarif_per_jam)) echo "  Tarif per Jam: {$record->tarif_per_jam}\n";
                    if (isset($record->kapasitas_per_jam)) echo "  Kapasitas per Jam: {$record->kapasitas_per_jam}\n";
                    echo "  ---\n";
                }
            }
        } else {
            echo "Tabel tidak ditemukan!\n";
        }
        echo "\n";
    }

    // 4. Cek spesifik tabel yang sudah kita ketahui
    echo "=== Memeriksa Tabel Spesifik ===\n";
    
    $specificTables = ['btkls', 'proses_produksis', 'bop_proses'];
    foreach ($specificTables as $table) {
        if (Schema::hasTable($table)) {
            echo "\n--- Tabel: $table ---\n";
            $count = DB::table($table)->count();
            echo "Total records: $count\n";
            
            if ($count > 0) {
                $sample = DB::table($tableName)->limit(1)->get();
                foreach ($sample as $record) {
                    $data = json_encode($record, JSON_PRETTY_PRINT);
                    echo "Contoh data:\n$data\n";
                }
            }
        } else {
            echo "\n--- Tabel: $table --- TIDAK DITEMUKAN\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

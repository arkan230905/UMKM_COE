<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== CEK DETAIL jurnal_umum.coa_id ===\n\n";

// 1. Cek detail kolom coa_id
$col = DB::select("
    SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'jurnal_umum'
      AND COLUMN_NAME = 'coa_id'
")[0] ?? null;

if ($col) {
    echo "coa_id:\n";
    echo "  COLUMN_TYPE : {$col->COLUMN_TYPE}\n";
    echo "  IS_NULLABLE : {$col->IS_NULLABLE}\n";
    echo "  DEFAULT     : {$col->COLUMN_DEFAULT}\n\n";
} else {
    echo "KOLOM coa_id TIDAK DITEMUKAN!\n\n";
}

// 2. Cek semua FK yang ada di jurnal_umum sekarang
$fks = DB::select("
    SELECT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE kcu
    WHERE kcu.TABLE_SCHEMA = DATABASE()
      AND kcu.TABLE_NAME = 'jurnal_umum'
      AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
");

echo "FK yang ada di jurnal_umum:\n";
if (empty($fks)) {
    echo "  (tidak ada FK)\n";
} else {
    foreach ($fks as $fk) {
        echo "  {$fk->CONSTRAINT_NAME}: {$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
    }
}

// 3. Cek detail coas.id
$coasId = DB::select("
    SELECT COLUMN_TYPE, IS_NULLABLE, EXTRA
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'coas'
      AND COLUMN_NAME = 'id'
")[0] ?? null;

echo "\ncoas.id:\n";
echo "  COLUMN_TYPE : {$coasId->COLUMN_TYPE}\n";
echo "  IS_NULLABLE : {$coasId->IS_NULLABLE}\n";
echo "  EXTRA       : {$coasId->EXTRA}\n";

// 4. Cek apakah coas punya index pada id
$indexes = DB::select("SHOW INDEX FROM `coas` WHERE Key_name = 'PRIMARY'");
echo "\ncoas PRIMARY KEY: " . (empty($indexes) ? "TIDAK ADA!" : "ADA ✅") . "\n";

// 5. Cek engine tabel
$engines = DB::select("
    SELECT TABLE_NAME, ENGINE
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME IN ('jurnal_umum', 'coas')
");
echo "\nEngine tabel:\n";
foreach ($engines as $e) {
    echo "  {$e->TABLE_NAME}: {$e->ENGINE}\n";
}

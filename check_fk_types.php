<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK TIPE DATA KOLOM UNTUK FK ===\n\n";

// Cek tipe data id di coas
$coasId = DB::select("
    SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, EXTRA
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'coas'
      AND COLUMN_NAME = 'id'
")[0] ?? null;

echo "coas.id => " . ($coasId ? "{$coasId->COLUMN_TYPE} | nullable={$coasId->IS_NULLABLE} | extra={$coasId->EXTRA}" : "NOT FOUND") . "\n\n";

// Cek semua kolom yang akan di-FK ke coas
$targets = [
    'jurnal_umum'       => ['coa_id'],
    'produksis'         => ['coa_persediaan_barang_jadi_id'],
    'bop_budgets'       => ['coa_id'],
    'bops'              => ['coa_id'],
    'pembayaran_bebans' => ['akun_beban_id', 'akun_kas_id'],
    'pelunasan_utangs'  => ['akun_kas_id'],
    'journal_lines'     => ['coa_id'],
];

echo str_repeat("-", 90) . "\n";
printf("%-25s %-30s %-20s %-10s\n", "Tabel", "Kolom", "Tipe", "Match?");
echo str_repeat("-", 90) . "\n";

foreach ($targets as $table => $columns) {
    foreach ($columns as $col) {
        $row = DB::select("
            SELECT COLUMN_TYPE, IS_NULLABLE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ", [$table, $col])[0] ?? null;

        if (!$row) {
            printf("%-25s %-30s %-20s %-10s\n", $table, $col, "COLUMN NOT FOUND", "❌");
            continue;
        }

        // coas.id biasanya bigint unsigned
        $match = (stripos($row->COLUMN_TYPE, 'int') !== false) ? "✅" : "❌ MISMATCH";
        printf("%-25s %-30s %-20s %-10s\n", $table, $col, $row->COLUMN_TYPE, $match);
    }
}
echo str_repeat("-", 90) . "\n";

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get all pending migrations
$migrations = DB::table('migrations')->pluck('migration')->toArray();

// Migrations that try to add user_id but column already exists
$duplicateMigrations = [
    '2026_05_05_010935_add_user_id_to_pegawais_table',
    '2026_05_05_011750_add_user_id_to_pelanggans_table',
    '2026_05_05_015703_add_user_id_to_kategori_bahan_pendukung_table',
    '2026_05_05_024539_add_user_id_to_bom_job_costings_table',
    '2026_05_05_024606_add_user_id_to_bom_job_bbb_table',
    '2026_05_05_024629_add_user_id_to_bom_job_bahan_pendukung_table',
    '2026_05_05_055705_add_user_id_to_proses_produksis_table',
    '2026_05_05_060012_add_user_id_to_komponen_bops_table',
    '2026_05_05_062056_add_user_id_to_kategori_pegawai_table',
    '2026_05_05_063623_add_user_id_to_bops_tables',
    '2026_05_05_202002_add_user_id_and_proses_produksi_id_to_produksi_proses_table',
    '2026_05_06_000001_add_user_id_to_retur_penjualans_table',
    '2026_05_06_050951_add_user_id_to_purchase_returns_table',
    '2026_05_06_052224_add_user_id_to_stock_movements_table',
    '2026_05_06_052243_add_user_id_to_stock_layers_table',
    '2026_05_06_052301_add_user_id_to_pembelian_detail_konversi_table',
    '2026_05_06_080000_add_user_id_to_presensi_users_table',
    '2026_05_06_080001_add_user_id_to_presensi_records_table',
    '2026_05_06_080002_add_user_id_to_verifikasi_wajahs_table',
    '2026_05_07_014103_add_user_id_to_produksi_proses_table',
];

$batch = DB::table('migrations')->max('batch') + 1;

foreach ($duplicateMigrations as $migration) {
    if (!in_array($migration, $migrations)) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch
        ]);
        echo "✅ Marked as run: $migration\n";
    } else {
        echo "⏭️  Already run: $migration\n";
    }
}

echo "\n✅ Done! Now run: php artisan migrate\n";

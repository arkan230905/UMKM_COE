<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixPendingMigrations extends Migration
{
    public function up()
    {
        // Mark the problematic migration as run
        if (!DB::table('migrations')->where('migration', '2025_10_29_162000_add_budget_to_bops_table')->exists()) {
            DB::table('migrations')->insert([
                'migration' => '2025_10_29_162000_add_budget_to_bops_table',
                'batch' => DB::table('migrations')->max('batch') + 1
            ]);
        }

        // Ensure the bops table has the required columns
        if (Schema::hasTable('bops')) {
            if (!Schema::hasColumn('bops', 'budget')) {
                Schema::table('bops', function (Blueprint $table) {
                    if (Schema::hasColumn('bops', 'keterangan')) {
                        $table->decimal('budget', 15, 2)->default(0)->after('keterangan');
                    } else {
                        $table->decimal('budget', 15, 2)->default(0);
                    }
                });
            }
        }

        // Mark other problematic migrations as run
        $migrationsToSkip = [
            '2025_10_29_163219_add_kategori_tenaga_kerja_to_pegawais_table',
            '2025_10_29_172802_add_tarif_per_jam_to_pegawais_table',
            '2025_10_29_183709_fix_pegawais_table_structure',
            '2025_10_29_183915_fix_presensis_table_structure',
            '2025_10_29_184500_fix_pegawai_structure',
            '2025_10_29_184806_fix_pegawai_presensi_relationship',
            '2025_10_29_192402_add_tanggal_status_to_pegawais_table',
            '2025_10_29_210000_add_missing_fields_to_asets',
            '2025_10_29_220623_fix_presensi_table_columns',
            '2025_10_29_231002_add_satuan_id_to_bahan_bakus_table',
            '2025_10_29_231531_update_bahan_bakus_table',
            '2025_10_30_000000_create_jenis_asets_table',
            '2025_10_30_000001_create_kategori_asets_table',
            '2025_10_30_000002_update_asets_table',
            '2025_10_30_115236_fix_bom_structure_final',
            '2025_10_30_115900_create_sessions_table',
            '2025_10_30_120114_add_username_and_phone_to_users_table',
            '2025_10_30_123000_modify_phone_column_in_users_table',
            '2025_11_02_000001_create_asets_table',
            '2025_11_02_000002_create_depreciation_schedules_table',
            '2025_11_02_235300_cleanup_asets_table',
            '2025_11_03_000000_align_asets_schema',
            '2025_11_03_003400_align_pegawais_schema',
            '2025_11_03_004900_backfill_pegawais_nomor_induk',
            '2025_11_03_010300_align_presensis_schema',
            '2025_11_03_011500_rebuild_presensis_table',
            '2025_11_03_013950_add_keterangan_to_presensis',
            '2025_11_03_101000_add_username_phone_to_users_table',
            '2025_11_03_103700_sync_asets_table_schema',
            '2025_11_03_104000_rebuild_asets_table',
            '2025_11_03_105100_add_kode_aset_column',
            '2025_11_05_000001_create_jabatans_table',
            '2025_11_05_100001_create_assets_table',
            '2025_11_05_100002_create_asset_depreciations_table',
            '2025_11_05_103900_update_assets_table_for_example_fields',
            '2025_11_05_113800_align_coas_schema',
            '2025_11_05_115053_add_satuan_id_to_bahan_bakus_fix',
            '2025_11_05_131000_add_fields_to_jabatans_table',
            '2025_11_06_103200_add_saldo_awal_dates_to_coas',
            '2025_11_07_153218_fix_duplicate_budget_column_in_bops_table',
            '2025_11_07_155021_fix_foreign_key_constraints',
            '2025_11_07_155500_fix_coas_bops_relation',
            '2025_11_07_160000_skip_problematic_migration',
            '2025_11_07_232455_add_missing_columns_to_coas_table',
            '2025_11_07_232746_drop_bops_foreign_key',
            '2025_11_07_232804_fix_coas_table_structure',
            '2025_11_07_232905_fix_coas_table_structure_v2',
            '2025_11_07_233008_delete_problematic_coas_migration',
            '2025_11_07_233139_fix_coas_table_final',
            '2025_11_08_000000_fix_pegawais_structure',
            '2025_11_08_000001_add_missing_columns_to_coas_table',
            '2025_11_08_000002_drop_bops_table',
            '2025_11_08_000003_fix_foreign_keys',
            '2025_11_08_000004_add_missing_columns_to_coas_safely',
            '2025_11_08_000005_fix_coas_table_final',
            '2025_11_08_000006_fix_bops_foreign_key',
            '2025_11_08_000007_fix_coas_table_directly',
            '2025_11_08_000008_add_missing_columns_to_coas',
            '2025_11_08_000009_fix_coas_columns',
            '2025_11_08_000010_create_jabatans_table_final',
            '2025_11_08_000011_add_kode_pegawai_to_pegawais_table',
            '2025_11_08_000012_update_existing_pegawais_with_kode',
            '2025_11_08_000013_add_keterangan_to_presensis_table',
            '2025_11_08_000014_add_satuan_id_to_bahan_bakus_table'
        ];

        $batch = DB::table('migrations')->max('batch') + 1;
        foreach ($migrationsToSkip as $migration) {
            if (!DB::table('migrations')->where('migration', $migration)->exists()) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $batch
                ]);
            }
        }
    }

    public function down()
    {
        // This is a one-way migration to prevent data loss
    }
}

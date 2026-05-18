<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drop semua foreign key yang masih mereferensikan tabel 'accounts' (tabel lama)
 * dan ganti ke tabel 'coas' (tabel baru) menggunakan raw SQL.
 *
 * Hasil check_fk_types.php:
 *  - Semua kolom sudah bigint(20) unsigned → cocok dengan coas.id
 *  - pembayaran_bebans.akun_beban_id & akun_kas_id → TIDAK ADA, skip
 */
return new class extends Migration
{
    /** [tabel => [kolom]] — hanya yang CONFIRMED ada di database */
    private array $targets = [
        'jurnal_umum'    => ['coa_id'],
        'produksis'      => ['coa_persediaan_barang_jadi_id'],
        'bop_budgets'    => ['coa_id'],
        'bops'           => ['coa_id'],
        'pelunasan_utangs' => ['akun_kas_id'],
        'journal_lines'  => ['coa_id'],
    ];

    public function up(): void
    {
        foreach ($this->targets as $table => $columns) {
            if (!Schema::hasTable($table)) continue;

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) continue;

                // 1. Drop semua FK lama pada kolom ini (ke tabel apapun)
                $this->dropAllForeignKeys($table, $column);

                // 2. Cek apakah FK ke coas sudah ada
                $exists = DB::select("
                    SELECT kcu.CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE kcu
                    WHERE kcu.TABLE_SCHEMA          = DATABASE()
                      AND kcu.TABLE_NAME            = ?
                      AND kcu.COLUMN_NAME           = ?
                      AND kcu.REFERENCED_TABLE_NAME = 'coas'
                ", [$table, $column]);

                if (empty($exists)) {
                    // 3. Tambah FK baru ke coas
                    $fkName = substr("{$table}_{$column}_coas_fk", 0, 64);
                    DB::statement("
                        ALTER TABLE `{$table}`
                        ADD CONSTRAINT `{$fkName}`
                        FOREIGN KEY (`{$column}`)
                        REFERENCES `coas` (`id`)
                        ON DELETE SET NULL
                    ");
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->targets as $table => $columns) {
            if (!Schema::hasTable($table)) continue;
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) continue;
                $this->dropAllForeignKeys($table, $column, 'coas');
            }
        }
    }

    private function dropAllForeignKeys(string $table, string $column, ?string $refTable = null): void
    {
        $sql = "
            SELECT kcu.CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
              ON rc.CONSTRAINT_NAME   = kcu.CONSTRAINT_NAME
             AND rc.CONSTRAINT_SCHEMA = kcu.TABLE_SCHEMA
            WHERE kcu.TABLE_SCHEMA = DATABASE()
              AND kcu.TABLE_NAME   = ?
              AND kcu.COLUMN_NAME  = ?
              AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
        ";
        $params = [$table, $column];

        if ($refTable) {
            $sql .= " AND kcu.REFERENCED_TABLE_NAME = ?";
            $params[] = $refTable;
        }

        foreach (DB::select($sql, $params) as $fk) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Drop semua foreign key yang masih mereferensikan tabel 'accounts' (tabel lama)
 * dan ganti ke tabel 'coas' (tabel baru)
 *
 * Tabel yang terdampak:
 *  - jurnal_umum        (coa_id)
 *  - produksis          (coa_persediaan_barang_jadi_id)
 *  - bop_budgets        (coa_id)
 *  - bops               (coa_id)
 *  - pembayaran_bebans  (akun_beban_id, akun_kas_id)
 *  - pelunasan_utangs   (akun_kas_id)
 */
return new class extends Migration
{
    /** Daftar [tabel => [kolom, ...]] yang perlu di-fix */
    private array $targets = [
        'jurnal_umum'       => ['coa_id'],
        'produksis'         => ['coa_persediaan_barang_jadi_id'],
        'bop_budgets'       => ['coa_id'],
        'bops'              => ['coa_id'],
        'pembayaran_bebans' => ['akun_beban_id', 'akun_kas_id'],
        'pelunasan_utangs'  => ['akun_kas_id'],
        'journal_lines'     => ['coa_id'],
    ];

    public function up(): void
    {
        foreach ($this->targets as $table => $columns) {
            if (!Schema::hasTable($table)) continue;

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) continue;

                // Cari semua FK pada kolom ini
                $fks = DB::select("
                    SELECT kcu.CONSTRAINT_NAME, kcu.REFERENCED_TABLE_NAME
                    FROM information_schema.KEY_COLUMN_USAGE kcu
                    JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                      ON rc.CONSTRAINT_NAME    = kcu.CONSTRAINT_NAME
                     AND rc.CONSTRAINT_SCHEMA  = kcu.TABLE_SCHEMA
                    WHERE kcu.TABLE_SCHEMA      = DATABASE()
                      AND kcu.TABLE_NAME        = ?
                      AND kcu.COLUMN_NAME       = ?
                      AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                ", [$table, $column]);

                foreach ($fks as $fk) {
                    // Drop FK lama (apapun tabelnya)
                    DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }

                // Tambah FK baru ke coas (nullable-safe)
                Schema::table($table, function (Blueprint $tbl) use ($column) {
                    $tbl->foreign($column)
                        ->references('id')
                        ->on('coas')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // Drop FK ke coas yang baru saja dibuat
        foreach ($this->targets as $table => $columns) {
            if (!Schema::hasTable($table)) continue;

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) continue;

                $fks = DB::select("
                    SELECT kcu.CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE kcu
                    JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                      ON rc.CONSTRAINT_NAME   = kcu.CONSTRAINT_NAME
                     AND rc.CONSTRAINT_SCHEMA = kcu.TABLE_SCHEMA
                    WHERE kcu.TABLE_SCHEMA     = DATABASE()
                      AND kcu.TABLE_NAME       = ?
                      AND kcu.COLUMN_NAME      = ?
                      AND kcu.REFERENCED_TABLE_NAME = 'coas'
                ", [$table, $column]);

                foreach ($fks as $fk) {
                    DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }
            }
        }
    }
};

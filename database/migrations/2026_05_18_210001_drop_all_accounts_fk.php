<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drop semua foreign key yang masih mereferensikan tabel 'accounts' (tabel lama).
 * TIDAK menambah FK baru ke coas — cukup hapus constraint yang salah.
 *
 * Alasan tidak tambah FK baru:
 *  - Beberapa kolom NOT NULL sehingga ON DELETE SET NULL tidak valid
 *  - Aplikasi sudah berjalan dengan coa_id sebagai integer biasa
 *  - Menghapus FK lama sudah cukup untuk menghilangkan error constraint violation
 */
return new class extends Migration
{
    private array $targets = [
        'jurnal_umum'      => ['coa_id'],
        'produksis'        => ['coa_persediaan_barang_jadi_id'],
        'bop_budgets'      => ['coa_id'],
        'bops'             => ['coa_id'],
        'pelunasan_utangs' => ['akun_kas_id'],
        'journal_lines'    => ['coa_id'],
    ];

    public function up(): void
    {
        foreach ($this->targets as $table => $columns) {
            if (!Schema::hasTable($table)) continue;

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) continue;

                // Drop semua FK pada kolom ini (ke tabel apapun)
                $fks = DB::select("
                    SELECT kcu.CONSTRAINT_NAME, kcu.REFERENCED_TABLE_NAME
                    FROM information_schema.KEY_COLUMN_USAGE kcu
                    JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                      ON rc.CONSTRAINT_NAME   = kcu.CONSTRAINT_NAME
                     AND rc.CONSTRAINT_SCHEMA = kcu.TABLE_SCHEMA
                    WHERE kcu.TABLE_SCHEMA = DATABASE()
                      AND kcu.TABLE_NAME   = ?
                      AND kcu.COLUMN_NAME  = ?
                      AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                ", [$table, $column]);

                foreach ($fks as $fk) {
                    DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }
            }
        }
    }

    public function down(): void
    {
        // Tidak bisa restore FK lama ke accounts karena tabel accounts sudah tidak relevan
    }
};

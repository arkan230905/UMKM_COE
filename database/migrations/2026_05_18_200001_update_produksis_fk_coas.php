<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop ALL foreign keys on coa_persediaan_barang_jadi_id using raw SQL
        // This handles any constraint name variation
        $fks = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'produksis'
              AND COLUMN_NAME = 'coa_persediaan_barang_jadi_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `produksis` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Re-add foreign key pointing to coas (not accounts)
        // Only if column exists
        if (Schema::hasColumn('produksis', 'coa_persediaan_barang_jadi_id')) {
            Schema::table('produksis', function (Blueprint $table) {
                $table->foreign('coa_persediaan_barang_jadi_id')
                      ->references('id')
                      ->on('coas')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Drop the coas FK
        $fks = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'produksis'
              AND COLUMN_NAME = 'coa_persediaan_barang_jadi_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `produksis` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
    }
};

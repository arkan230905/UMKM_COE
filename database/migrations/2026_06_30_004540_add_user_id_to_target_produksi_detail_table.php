<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Tambah kolom user_id sebagai nullable terlebih dahulu (jika belum ada)
        if (!Schema::hasColumn('target_produksi_detail', 'user_id')) {
            Schema::table('target_produksi_detail', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            });
        }
        
        // Step 2: Backfill user_id dari target_produksi
        DB::statement('
            UPDATE target_produksi_detail tpd
            INNER JOIN target_produksi tp ON tp.id = tpd.target_produksi_id
            SET tpd.user_id = tp.user_id
            WHERE tpd.user_id IS NULL OR tpd.user_id = 0
        ');
        
        // Step 3: Ubah kolom user_id menjadi NOT NULL
        DB::statement('ALTER TABLE target_produksi_detail MODIFY user_id BIGINT UNSIGNED NOT NULL');
        
        // Step 4: Tambahkan foreign key jika belum ada
        $foreignKeyExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = 'target_produksi_detail'
            AND CONSTRAINT_NAME = 'target_produksi_detail_user_id_foreign'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ")[0]->count;
        
        if ($foreignKeyExists == 0) {
            Schema::table('target_produksi_detail', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_produksi_detail', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

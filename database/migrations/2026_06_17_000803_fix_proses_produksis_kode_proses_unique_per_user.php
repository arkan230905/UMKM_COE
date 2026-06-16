<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: Mengubah unique constraint pada kode_proses menjadi unique per user (multi-tenant)
     */
    public function up(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Drop unique constraint lama yang global
            $table->dropUnique('proses_produksis_kode_proses_unique');
            
            // Tambah composite unique constraint: kode_proses + user_id
            $table->unique(['user_id', 'kode_proses'], 'proses_produksis_user_kode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Kembalikan ke unique constraint global
            $table->dropUnique('proses_produksis_user_kode_unique');
            $table->unique('kode_proses', 'proses_produksis_kode_proses_unique');
        });
    }
};

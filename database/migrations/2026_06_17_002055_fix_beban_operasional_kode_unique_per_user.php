<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: Mengubah unique constraint pada kode menjadi unique per user (multi-tenant)
     */
    public function up(): void
    {
        Schema::table('beban_operasional', function (Blueprint $table) {
            // Drop unique constraint lama yang global
            $table->dropUnique('beban_operasional_kode_unique');
            
            // Tambah composite unique constraint: user_id + kode
            $table->unique(['user_id', 'kode'], 'beban_operasional_user_kode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beban_operasional', function (Blueprint $table) {
            // Kembalikan ke unique constraint global
            $table->dropUnique('beban_operasional_user_kode_unique');
            $table->unique('kode', 'beban_operasional_kode_unique');
        });
    }
};

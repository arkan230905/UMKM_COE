<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * We must drop FK constraints that reference pegawais.kode_pegawai before
     * we can drop the unique index on that column (MySQL requirement: error 1553).
     * After restructuring the index we re-add the FKs and keep a standalone
     * index on kode_pegawai so MySQL has a usable index for the FK reference.
     */
    public function up(): void
    {
        // 1. Drop foreign keys that reference pegawais.kode_pegawai
        if (Schema::hasTable('verifikasi_wajahs')) {
            Schema::table('verifikasi_wajahs', function (Blueprint $table) {
                $table->dropForeign(['nomor_induk_pegawai']);
            });
        }

        if (Schema::hasTable('verifikasi_wajah')) {
            Schema::table('verifikasi_wajah', function (Blueprint $table) {
                $table->dropForeign(['kode_pegawai']);
            });
        }

        // 2. Replace the single-column unique with a composite unique,
        //    and add a plain index on kode_pegawai for FK backing.
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropUnique('pegawais_kode_pegawai_unique');
            $table->unique(['user_id', 'kode_pegawai'], 'pegawais_user_id_kode_pegawai_unique');
            // Plain index so the FK references still have an index to use
            $table->index('kode_pegawai', 'pegawais_kode_pegawai_index');
        });

        // 3. Re-add the foreign keys
        if (Schema::hasTable('verifikasi_wajahs')) {
            Schema::table('verifikasi_wajahs', function (Blueprint $table) {
                $table->foreign('nomor_induk_pegawai')
                      ->references('kode_pegawai')->on('pegawais')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('verifikasi_wajah')) {
            Schema::table('verifikasi_wajah', function (Blueprint $table) {
                $table->foreign('kode_pegawai')
                      ->references('kode_pegawai')->on('pegawais')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop foreign keys
        if (Schema::hasTable('verifikasi_wajahs')) {
            Schema::table('verifikasi_wajahs', function (Blueprint $table) {
                $table->dropForeign(['nomor_induk_pegawai']);
            });
        }

        if (Schema::hasTable('verifikasi_wajah')) {
            Schema::table('verifikasi_wajah', function (Blueprint $table) {
                $table->dropForeign(['kode_pegawai']);
            });
        }

        // 2. Restore the original single-column unique index
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropUnique('pegawais_user_id_kode_pegawai_unique');
            $table->dropIndex('pegawais_kode_pegawai_index');
            $table->unique('kode_pegawai', 'pegawais_kode_pegawai_unique');
        });

        // 3. Re-add the foreign keys
        if (Schema::hasTable('verifikasi_wajahs')) {
            Schema::table('verifikasi_wajahs', function (Blueprint $table) {
                $table->foreign('nomor_induk_pegawai')
                      ->references('kode_pegawai')->on('pegawais')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('verifikasi_wajah')) {
            Schema::table('verifikasi_wajah', function (Blueprint $table) {
                $table->foreign('kode_pegawai')
                      ->references('kode_pegawai')->on('pegawais')->onDelete('cascade');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop foreign keys referencing jabatans
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
        });

        Schema::table('btkls', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
        });

        // 2. Rename the main table
        Schema::rename('jabatans', 'kualifikasis');

        // 3. Rename columns in kualifikasis
        Schema::table('kualifikasis', function (Blueprint $table) {
            $table->renameColumn('kode_jabatan', 'kode_kualifikasi');
            $table->renameColumn('nama', 'nama_kualifikasi');
        });

        // 4. Rename columns in pegawais
        Schema::table('pegawais', function (Blueprint $table) {
            $table->renameColumn('jabatan_id', 'kualifikasi_id');
            $table->renameColumn('jabatan', 'kualifikasi');
        });

        // 5. Rename columns in btkls
        Schema::table('btkls', function (Blueprint $table) {
            $table->renameColumn('jabatan_id', 'kualifikasi_id');
        });

        // 6. Re-add foreign keys pointing to kualifikasis
        Schema::table('pegawais', function (Blueprint $table) {
            $table->foreign('kualifikasi_id')->references('id')->on('kualifikasis')->onDelete('set null');
        });

        Schema::table('btkls', function (Blueprint $table) {
            $table->foreign('kualifikasi_id')->references('id')->on('kualifikasis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropForeign(['kualifikasi_id']);
        });

        Schema::table('btkls', function (Blueprint $table) {
            $table->dropForeign(['kualifikasi_id']);
        });

        Schema::table('btkls', function (Blueprint $table) {
            $table->renameColumn('kualifikasi_id', 'jabatan_id');
        });

        Schema::table('pegawais', function (Blueprint $table) {
            $table->renameColumn('kualifikasi_id', 'jabatan_id');
            $table->renameColumn('kualifikasi', 'jabatan');
        });

        Schema::table('kualifikasis', function (Blueprint $table) {
            $table->renameColumn('nama_kualifikasi', 'nama');
            $table->renameColumn('kode_kualifikasi', 'kode_jabatan');
        });

        Schema::rename('kualifikasis', 'jabatans');

        Schema::table('pegawais', function (Blueprint $table) {
            $table->foreign('jabatan_id')->references('id')->on('jabatans')->onDelete('set null');
        });

        Schema::table('btkls', function (Blueprint $table) {
            $table->foreign('jabatan_id')->references('id')->on('jabatans')->onDelete('cascade');
        });
    }
};

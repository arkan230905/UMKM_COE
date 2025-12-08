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
        Schema::table('pegawais', function (Blueprint $table) {
            // Tambah kolom yang hilang jika belum ada
            if (!Schema::hasColumn('pegawais', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->default(0)->after('gaji');
            }
            if (!Schema::hasColumn('pegawais', 'tarif_per_jam')) {
                $table->decimal('tarif_per_jam', 15, 2)->default(0)->after('gaji_pokok');
            }
            if (!Schema::hasColumn('pegawais', 'asuransi')) {
                $table->decimal('asuransi', 15, 2)->default(0)->after('tunjangan');
            }
            if (!Schema::hasColumn('pegawais', 'bank')) {
                $table->string('bank', 100)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'nomor_rekening')) {
                $table->string('nomor_rekening', 50)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'nama_rekening')) {
                $table->string('nama_rekening', 100)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->string('kode_pegawai', 20)->nullable()->after('id');
            }
            if (!Schema::hasColumn('pegawais', 'jenis_pegawai')) {
                $table->string('jenis_pegawai', 10)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $columns = ['gaji_pokok', 'tarif_per_jam', 'asuransi', 'bank', 'nomor_rekening', 'nama_rekening', 'kode_pegawai', 'jenis_pegawai'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('pegawais', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

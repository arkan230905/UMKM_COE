<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Tambahkan kolom gaji_pokok jika belum ada
            if (!Schema::hasColumn('pegawais', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->nullable()->after('gaji');
            }
            
            // Tambahkan kolom tarif_per_jam jika belum ada
            if (!Schema::hasColumn('pegawais', 'tarif_per_jam')) {
                $table->decimal('tarif_per_jam', 15, 2)->nullable()->after('gaji_pokok');
            }
            
            // Tambahkan kolom tunjangan jika belum ada
            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->nullable()->after('tarif_per_jam');
            }
            
            // Tambahkan kolom asuransi jika belum ada
            if (!Schema::hasColumn('pegawais', 'asuransi')) {
                $table->decimal('asuransi', 15, 2)->nullable()->after('tunjangan');
            }
            
            // Tambahkan kolom jenis_pegawai jika belum ada
            if (!Schema::hasColumn('pegawais', 'jenis_pegawai')) {
                $table->enum('jenis_pegawai', ['BTKL', 'BTKTL'])->nullable()->after('asuransi');
            }
            
            // Tambahkan kolom bank jika belum ada
            if (!Schema::hasColumn('pegawais', 'bank')) {
                $table->string('bank')->nullable()->after('jenis_pegawai');
            }
            
            // Tambahkan kolom nomor_rekening jika belum ada
            if (!Schema::hasColumn('pegawais', 'nomor_rekening')) {
                $table->string('nomor_rekening')->nullable()->after('bank');
            }
            
            // Tambahkan kolom nama_rekening jika belum ada
            if (!Schema::hasColumn('pegawais', 'nama_rekening')) {
                $table->string('nama_rekening')->nullable()->after('nomor_rekening');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $columns = [
                'gaji_pokok',
                'tarif_per_jam',
                'tunjangan',
                'asuransi',
                'jenis_pegawai',
                'bank',
                'nomor_rekening',
                'nama_rekening'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('pegawais', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

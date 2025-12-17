<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'no_telp')) {
                $table->string('no_telp')->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'tarif_per_jam')) {
                $table->decimal('tarif_per_jam', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'asuransi')) {
                $table->decimal('asuransi', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'jenis_pegawai')) {
                $table->string('jenis_pegawai')->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'bank')) {
                $table->string('bank')->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'nomor_rekening')) {
                $table->string('nomor_rekening')->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'nama_rekening')) {
                $table->string('nama_rekening')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $columns = [
                'no_telp',
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Ensure all required columns exist
            if (!Schema::hasColumn('pegawais', 'kategori_tenaga_kerja')) {
                $table->enum('kategori_tenaga_kerja', ['BTKL', 'BTKTL'])->default('BTKL')->after('jabatan');
            }
            if (!Schema::hasColumn('pegawais', 'tarif_per_jam')) {
                $table->decimal('tarif_per_jam', 12, 2)->default(0)->after('gaji_pokok');
            }
            if (!Schema::hasColumn('pegawais', 'asuransi')) {
                $table->decimal('asuransi', 12, 2)->default(0)->after('tunjangan');
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
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $columns = ['kategori_tenaga_kerja', 'bank', 'nomor_rekening', 'nama_rekening', 'tarif_per_jam', 'asuransi'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('pegawais', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

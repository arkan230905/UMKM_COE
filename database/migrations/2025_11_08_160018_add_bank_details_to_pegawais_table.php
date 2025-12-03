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
            if (!Schema::hasColumn('pegawais', 'bank')) {
                $table->string('bank', 100)->nullable()->after('jenis_pegawai');
            }
            if (!Schema::hasColumn('pegawais', 'nomor_rekening')) {
                $table->string('nomor_rekening', 50)->nullable()->after('bank');
            }
            if (!Schema::hasColumn('pegawais', 'nama_rekening')) {
                $table->string('nama_rekening', 100)->nullable()->after('nomor_rekening');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $columns = ['bank', 'nomor_rekening', 'nama_rekening'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('pegawais', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

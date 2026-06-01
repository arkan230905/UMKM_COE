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
        Schema::table('asets', function (Blueprint $table) {
            if (!Schema::hasColumn('asets', 'jenis_aset')) {
                $table->enum('jenis_aset', ['Aset Tetap', 'Aset Tidak Tetap', 'Aset Tidak Berwujud', 'aset-tetap', 'aset-tidak-tetap', 'aset-tidak-berwujud'])->default('aset-tetap')->after('kategori_aset_id');
            } else {
                DB::statement("ALTER TABLE asets MODIFY COLUMN jenis_aset ENUM('Aset Tetap', 'Aset Tidak Tetap', 'Aset Tidak Berwujud', 'aset-tetap', 'aset-tidak-tetap', 'aset-tidak-berwujud') DEFAULT 'aset-tetap'");
            }

            if (!Schema::hasColumn('asets', 'amortisasi_per_tahun')) {
                $table->bigInteger('amortisasi_per_tahun')->default(0)->after('penyusutan_per_bulan');
            }
            if (!Schema::hasColumn('asets', 'akumulasi_amortisasi')) {
                $table->bigInteger('akumulasi_amortisasi')->default(0)->after('akumulasi_penyusutan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'amortisasi_per_tahun')) {
                $table->dropColumn('amortisasi_per_tahun');
            }
            if (Schema::hasColumn('asets', 'akumulasi_amortisasi')) {
                $table->dropColumn('akumulasi_amortisasi');
            }
        });
        
        DB::statement("ALTER TABLE asets MODIFY COLUMN jenis_aset ENUM('Aset Tetap', 'Aset Tidak Tetap', 'Aset Tidak Berwujud') DEFAULT 'Aset Tetap'");
    }
};

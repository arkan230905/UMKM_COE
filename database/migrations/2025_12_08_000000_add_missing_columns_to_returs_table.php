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
        Schema::table('returs', function (Blueprint $table) {
            // Add tanggal column if it doesn't exist
            if (!Schema::hasColumn('returs', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('id');
            }
            
            // Add other missing columns if they don't exist
            if (!Schema::hasColumn('returs', 'kode_retur')) {
                $table->string('kode_retur')->nullable()->after('tanggal');
            }
            
            if (!Schema::hasColumn('returs', 'referensi_kode')) {
                $table->string('referensi_kode')->nullable()->after('kode_retur');
            }
            
            if (!Schema::hasColumn('returs', 'referensi_id')) {
                $table->unsignedBigInteger('referensi_id')->nullable()->after('referensi_kode');
            }
            
            if (!Schema::hasColumn('returs', 'tipe_kompensasi')) {
                $table->enum('tipe_kompensasi', ['barang', 'uang'])->default('barang')->after('referensi_id');
            }
            
            if (!Schema::hasColumn('returs', 'total_nilai_retur')) {
                $table->decimal('total_nilai_retur', 15, 2)->default(0)->after('tipe_kompensasi');
            }
            
            if (!Schema::hasColumn('returs', 'nilai_kompensasi')) {
                $table->decimal('nilai_kompensasi', 15, 2)->default(0)->after('total_nilai_retur');
            }
            
            if (!Schema::hasColumn('returs', 'status')) {
                $table->enum('status', ['draft', 'diproses', 'selesai'])->default('draft')->after('nilai_kompensasi');
            }
            
            if (!Schema::hasColumn('returs', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            $columns = ['tanggal', 'kode_retur', 'referensi_kode', 'referensi_id', 'tipe_kompensasi', 'total_nilai_retur', 'nilai_kompensasi', 'status', 'keterangan'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('returs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

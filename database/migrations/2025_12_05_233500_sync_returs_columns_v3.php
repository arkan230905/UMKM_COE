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
            // Add new columns at safe positions (using first/after existing known columns)
            if (!Schema::hasColumn('returs', 'kode_retur')) {
                $table->string('kode_retur')->nullable()->first();
            }
            if (!Schema::hasColumn('returs', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('kode_retur');
            }
            if (!Schema::hasColumn('returs', 'referensi_kode')) {
                $table->string('referensi_kode')->nullable()->after('tanggal');
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
                $table->enum('status', ['draft', 'diproses', 'selesai'])->default('draft')->after('created_by');
            }

            // Make existing columns nullable
            $nullableCols = ['created_by', 'pembelian_id', 'jumlah'];
            foreach ($nullableCols as $col) {
                if (Schema::hasColumn('returs', $col)) {
                    $table->unsignedBigInteger($col)->nullable()->change();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            $addedColumns = [
                'kode_retur', 'tanggal', 'referensi_kode', 'referensi_id',
                'tipe_kompensasi', 'total_nilai_retur', 'nilai_kompensasi', 'status'
            ];
            foreach ($addedColumns as $col) {
                if (Schema::hasColumn('returs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

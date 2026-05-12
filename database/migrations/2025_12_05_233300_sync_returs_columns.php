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
            // Rename existing columns to match controller expectations
            if (Schema::hasColumn('returs', 'type') && !Schema::hasColumn('returs', 'tipe_kompensasi')) {
                $table->renameColumn('type', 'tipe_kompensasi');
            }
            if (Schema::hasColumn('returs', 'ref_id') && !Schema::hasColumn('returs', 'referensi_id')) {
                $table->renameColumn('ref_id', 'referensi_id');
            }
            if (Schema::hasColumn('returs', 'alasan') && !Schema::hasColumn('returs', 'keterangan')) {
                $table->renameColumn('alasan', 'keterangan');
            }
            if (Schema::hasColumn('returs', 'memo') && !Schema::hasColumn('returs', 'referensi_kode')) {
                $table->renameColumn('memo', 'referensi_kode');
            }
            if (Schema::hasColumn('returs', 'jumlah') && !Schema::hasColumn('returs', 'total_nilai_retur')) {
                $table->renameColumn('jumlah', 'total_nilai_retur');
            }

            // Add missing columns
            if (!Schema::hasColumn('returs', 'kode_retur')) {
                $table->string('kode_retur')->nullable()->after('id');
                $table->unique('kode_retur');
            }
            if (!Schema::hasColumn('returs', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('kode_retur');
            }
            if (!Schema::hasColumn('returs', 'nilai_kompensasi')) {
                $table->decimal('nilai_kompensasi', 15, 2)->default(0)->after('total_nilai_retur');
            }
            if (!Schema::hasColumn('returs', 'status')) {
                $table->enum('status', ['draft', 'diproses', 'selesai'])->default('draft')->after('keterangan');
            }

            // Make columns nullable if needed
            $columnsToNullable = ['referensi_id', 'referensi_kode', 'created_by', 'pembelian_id'];
            foreach ($columnsToNullable as $col) {
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
            // Reverse renames
            if (Schema::hasColumn('returs', 'tipe_kompensasi') && !Schema::hasColumn('returs', 'type')) {
                $table->renameColumn('tipe_kompensasi', 'type');
            }
            if (Schema::hasColumn('returs', 'referensi_id') && !Schema::hasColumn('returs', 'ref_id')) {
                $table->renameColumn('referensi_id', 'ref_id');
            }
            if (Schema::hasColumn('returs', 'keterangan') && !Schema::hasColumn('returs', 'alasan')) {
                $table->renameColumn('keterangan', 'alasan');
            }
            if (Schema::hasColumn('returs', 'referensi_kode') && !Schema::hasColumn('returs', 'memo')) {
                $table->renameColumn('referensi_kode', 'memo');
            }
            if (Schema::hasColumn('returs', 'total_nilai_retur') && !Schema::hasColumn('returs', 'jumlah')) {
                $table->renameColumn('total_nilai_retur', 'jumlah');
            }

            // Drop added columns
            $addedColumns = ['kode_retur', 'tanggal', 'nilai_kompensasi', 'status'];
            foreach ($addedColumns as $col) {
                if (Schema::hasColumn('returs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

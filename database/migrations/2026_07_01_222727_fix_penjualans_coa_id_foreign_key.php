<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix foreign key constraint untuk coa_id di tabel penjualans dan pembelians
     * Yang sebelumnya salah mereferensi ke tabel 'accounts', seharusnya ke 'coas'
     */
    public function up(): void
    {
        // ==========================================
        // FIX PENJUALANS
        // ==========================================
        // Drop existing foreign key constraint yang salah (referensi ke accounts)
        Schema::table('penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('penjualans', 'coa_id')) {
                try {
                    $table->dropForeign(['coa_id']);
                } catch (\Exception $e) {
                    \Log::info('Foreign key penjualans.coa_id tidak ditemukan: ' . $e->getMessage());
                }
            }
        });

        // Tambahkan foreign key baru yang benar (referensi ke coas)
        Schema::table('penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('penjualans', 'coa_id')) {
                $table->unsignedBigInteger('coa_id')->nullable()->change();
                
                $table->foreign('coa_id')
                    ->references('id')
                    ->on('coas')
                    ->onDelete('set null');
            }
        });

        // ==========================================
        // FIX PEMBELIANS
        // ==========================================
        // Drop existing foreign key constraint yang salah (referensi ke accounts)
        Schema::table('pembelians', function (Blueprint $table) {
            if (Schema::hasColumn('pembelians', 'coa_id')) {
                try {
                    $table->dropForeign(['coa_id']);
                } catch (\Exception $e) {
                    \Log::info('Foreign key pembelians.coa_id tidak ditemukan: ' . $e->getMessage());
                }
            }
        });

        // Tambahkan foreign key baru yang benar (referensi ke coas)
        Schema::table('pembelians', function (Blueprint $table) {
            if (Schema::hasColumn('pembelians', 'coa_id')) {
                $table->unsignedBigInteger('coa_id')->nullable()->change();
                
                $table->foreign('coa_id')
                    ->references('id')
                    ->on('coas')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback penjualans
        Schema::table('penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('penjualans', 'coa_id')) {
                $table->dropForeign(['coa_id']);
                
                $table->foreign('coa_id')
                    ->references('id')
                    ->on('accounts')
                    ->onDelete('set null');
            }
        });

        // Rollback pembelians
        Schema::table('pembelians', function (Blueprint $table) {
            if (Schema::hasColumn('pembelians', 'coa_id')) {
                $table->dropForeign(['coa_id']);
                
                $table->foreign('coa_id')
                    ->references('id')
                    ->on('accounts')
                    ->onDelete('set null');
            }
        });
    }
};

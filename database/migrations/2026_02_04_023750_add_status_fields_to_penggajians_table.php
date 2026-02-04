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
        Schema::table('penggajians', function (Blueprint $table) {
            // Tambahkan field status_pembayaran jika belum ada
            if (!Schema::hasColumn('penggajians', 'status_pembayaran')) {
                $table->string('status_pembayaran')->default('belum_lunas')->after('total_gaji');
            }
            
            // Tambahkan field tanggal_dibayar jika belum ada
            if (!Schema::hasColumn('penggajians', 'tanggal_dibayar')) {
                $table->date('tanggal_dibayar')->nullable()->after('status_pembayaran');
            }
            
            // Tambahkan field metode_pembayaran jika belum ada
            if (!Schema::hasColumn('penggajians', 'metode_pembayaran')) {
                $table->string('metode_pembayaran')->nullable()->after('tanggal_dibayar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            if (Schema::hasColumn('penggajians', 'status_pembayaran')) {
                $table->dropColumn('status_pembayaran');
            }
            if (Schema::hasColumn('penggajians', 'tanggal_dibayar')) {
                $table->dropColumn('tanggal_dibayar');
            }
            if (Schema::hasColumn('penggajians', 'metode_pembayaran')) {
                $table->dropColumn('metode_pembayaran');
            }
        });
    }
};

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
            // Tambahkan field status_posting untuk tracking apakah sudah diposting ke jurnal
            if (!Schema::hasColumn('penggajians', 'status_posting')) {
                $table->string('status_posting')->default('belum_posting')->after('metode_pembayaran');
            }

            // Tambahkan field tanggal_posting jika belum ada
            if (!Schema::hasColumn('penggajians', 'tanggal_posting')) {
                $table->date('tanggal_posting')->nullable()->after('status_posting');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            if (Schema::hasColumn('penggajians', 'status_posting')) {
                $table->dropColumn('status_posting');
            }
            if (Schema::hasColumn('penggajians', 'tanggal_posting')) {
                $table->dropColumn('tanggal_posting');
            }
        });
    }
};

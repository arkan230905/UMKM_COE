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
            // Tambahkan field keterangan jika belum ada
            if (!Schema::hasColumn('penggajians', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('metode_pembayaran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            if (Schema::hasColumn('penggajians', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }
};

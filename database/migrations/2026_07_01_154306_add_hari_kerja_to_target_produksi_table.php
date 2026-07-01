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
        Schema::table('target_produksi_detail', function (Blueprint $table) {
            $table->integer('hari_kerja')->nullable()->after('target_bulanan')->comment('Jumlah hari kerja dalam bulan ini');
            $table->decimal('target_per_hari', 10, 2)->nullable()->after('hari_kerja')->comment('Target produksi per hari (target_bulanan / hari_kerja)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_produksi_detail', function (Blueprint $table) {
            $table->dropColumn(['hari_kerja', 'target_per_hari']);
        });
    }
};

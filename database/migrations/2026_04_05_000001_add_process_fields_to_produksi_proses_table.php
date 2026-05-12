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
        Schema::table('produksi_proses', function (Blueprint $table) {
            // Add new fields for process management
            $table->decimal('estimasi_durasi', 8, 2)->nullable()->after('status');
            $table->decimal('kapasitas_per_jam', 8, 2)->nullable()->after('estimasi_durasi');
            $table->decimal('tarif_per_jam', 10, 2)->nullable()->after('kapasitas_per_jam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi_proses', function (Blueprint $table) {
            $table->dropColumn(['estimasi_durasi', 'kapasitas_per_jam', 'tarif_per_jam']);
        });
    }
};
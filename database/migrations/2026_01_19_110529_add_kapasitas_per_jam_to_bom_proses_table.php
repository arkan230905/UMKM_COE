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
        Schema::table('bom_proses', function (Blueprint $table) {
            $table->integer('kapasitas_per_jam')->default(0)->after('biaya_bop'); // Kapasitas produksi per jam
            $table->index('kapasitas_per_jam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom_proses', function (Blueprint $table) {
            $table->dropIndex(['kapasitas_per_jam']);
            $table->dropColumn('kapasitas_per_jam');
        });
    }
};

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
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->integer('kapasitas_per_jam')->default(0)->after('satuan_btkl'); // Kapasitas produksi per jam
            $table->foreignId('btkl_id')->nullable()->after('kapasitas_per_jam'); // Reference to BTKL
            $table->index('kapasitas_per_jam');
            $table->index('btkl_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->dropIndex(['kapasitas_per_jam']);
            $table->dropIndex(['btkl_id']);
            $table->dropColumn('kapasitas_per_jam');
            $table->dropForeign(['btkl_id']);
            $table->dropColumn('btkl_id');
        });
    }
};

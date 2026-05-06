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
            $table->foreignId('proses_produksi_id')->nullable()->after('produksi_id')->constrained('proses_produksis')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi_proses', function (Blueprint $table) {
            $table->dropForeign(['proses_produksi_id']);
            $table->dropColumn('proses_produksi_id');
        });
    }
};

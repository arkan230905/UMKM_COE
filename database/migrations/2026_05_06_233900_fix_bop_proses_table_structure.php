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
        // Make proses_produksi_id nullable for standalone BOP
        Schema::table('bop_proses', function (Blueprint $table) {
            // Check if column exists and is not nullable
            $table->unsignedBigInteger('proses_produksi_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->unsignedBigInteger('proses_produksi_id')->nullable(false)->change();
        });
    }
};

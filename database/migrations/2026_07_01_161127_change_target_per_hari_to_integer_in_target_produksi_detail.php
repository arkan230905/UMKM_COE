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
            // Change target_per_hari from decimal(10,2) to integer
            $table->integer('target_per_hari')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_produksi_detail', function (Blueprint $table) {
            // Revert back to decimal(10,2)
            $table->decimal('target_per_hari', 10, 2)->nullable()->change();
        });
    }
};

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
        Schema::table('boms', function (Blueprint $table) {
            // Make bahan_baku_id nullable since BOM now uses separate details table
            $table->unsignedBigInteger('bahan_baku_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Revert back to NOT NULL (though this might cause issues if there are existing NULL values)
            $table->unsignedBigInteger('bahan_baku_id')->nullable(false)->change();
        });
    }
};

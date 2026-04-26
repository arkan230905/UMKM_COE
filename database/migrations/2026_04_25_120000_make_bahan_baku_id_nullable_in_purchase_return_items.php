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
        Schema::table('purchase_return_items', function (Blueprint $table) {
            // Make bahan_baku_id nullable since items can be either bahan_baku or bahan_pendukung
            $table->unsignedBigInteger('bahan_baku_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table) {
            // Revert back to NOT NULL (this might cause issues if there are existing NULL values)
            $table->unsignedBigInteger('bahan_baku_id')->nullable(false)->change();
        });
    }
};
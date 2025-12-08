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
        Schema::table('returs', function (Blueprint $table) {
            // Ensure kompensasi column supports 'barang' and 'uang'
            // We'll modify it to enum with correct values
            $table->enum('kompensasi', ['barang', 'uang'])->default('barang')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values if needed
        Schema::table('returs', function (Blueprint $table) {
            $table->enum('kompensasi', ['sale', 'purchase'])->default('sale')->change();
        });
    }
};

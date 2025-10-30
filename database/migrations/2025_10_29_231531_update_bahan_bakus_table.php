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
        // Update harga_satuan untuk memastikan bisa menerima nilai 0
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->decimal('harga_satuan', 15, 2)->default(0)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke default nullable
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->decimal('harga_satuan', 15, 2)->default(null)->nullable()->change();
        });
    }
};

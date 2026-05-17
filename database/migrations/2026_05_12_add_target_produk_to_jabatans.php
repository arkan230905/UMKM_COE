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
        Schema::table('jabatans', function (Blueprint $table) {
            // Add target_produk_per_bulan column if it doesn't exist
            if (!Schema::hasColumn('jabatans', 'target_produk_per_bulan')) {
                $table->integer('target_produk_per_bulan')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            if (Schema::hasColumn('jabatans', 'target_produk_per_bulan')) {
                $table->dropColumn('target_produk_per_bulan');
            }
        });
    }
};

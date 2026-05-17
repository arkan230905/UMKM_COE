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
            // Rename tarif_per_jam to tarif_produk only if it exists
            if (Schema::hasColumn('jabatans', 'tarif_per_jam')) {
                $table->renameColumn('tarif_per_jam', 'tarif_produk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Rename back to tarif_per_jam only if tarif_produk exists
            if (Schema::hasColumn('jabatans', 'tarif_produk')) {
                $table->renameColumn('tarif_produk', 'tarif_per_jam');
            }
        });
    }
};

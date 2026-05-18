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
            // Add tarif_produk column if it doesn't exist
            if (!Schema::hasColumn('jabatans', 'tarif_produk')) {
                $table->decimal('tarif_produk', 15, 2)->default(0)->after('tarif');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            if (Schema::hasColumn('jabatans', 'tarif_produk')) {
                $table->dropColumn('tarif_produk');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add biaya_per_produk column to btkls table
     */
    public function up(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            if (!Schema::hasColumn('btkls', 'biaya_per_produk')) {
                $table->decimal('biaya_per_produk', 15, 2)->default(0)->comment('Biaya BTKL per produk')->after('kapasitas_per_jam');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            if (Schema::hasColumn('btkls', 'biaya_per_produk')) {
                $table->dropColumn('biaya_per_produk');
            }
        });
    }
};

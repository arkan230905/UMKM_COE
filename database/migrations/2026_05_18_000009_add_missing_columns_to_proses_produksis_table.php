<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add missing columns to proses_produksis table
     */
    public function up(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Add satuan_btkl column (missing from original migration)
            if (!Schema::hasColumn('proses_produksis', 'satuan_btkl')) {
                $table->string('satuan_btkl', 20)->default('jam')->comment('Satuan waktu (jam, menit, unit)')->after('tarif_btkl');
            }
            // Add btkl_id column
            if (!Schema::hasColumn('proses_produksis', 'btkl_id')) {
                $table->foreignId('btkl_id')->nullable()->constrained('btkls')->onDelete('cascade')->after('jabatan_id');
            }
            // Add kapasitas_per_jam column
            if (!Schema::hasColumn('proses_produksis', 'kapasitas_per_jam')) {
                $table->decimal('kapasitas_per_jam', 15, 2)->default(0)->comment('Kapasitas produksi per jam')->after('btkl_id');
            }
            // Add biaya_btkl_per_produk column
            if (!Schema::hasColumn('proses_produksis', 'biaya_btkl_per_produk')) {
                $table->decimal('biaya_btkl_per_produk', 15, 2)->default(0)->comment('Biaya BTKL per produk')->after('kapasitas_per_jam');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            if (Schema::hasColumn('proses_produksis', 'btkl_id')) {
                $table->dropForeign(['btkl_id']);
            }
            $table->dropColumn(['satuan_btkl', 'btkl_id', 'kapasitas_per_jam', 'biaya_btkl_per_produk']);
        });
    }
};

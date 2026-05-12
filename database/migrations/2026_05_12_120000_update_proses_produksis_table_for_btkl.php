<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update proses_produksis table to match BTKL create form structure
     */
    public function up(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Check if columns exist before dropping to avoid errors
            if (Schema::hasColumn('proses_produksis', 'satuan_btkl')) {
                $table->dropColumn('satuan_btkl');
            }
            if (Schema::hasColumn('proses_produksis', 'kapasitas_per_jam')) {
                $table->dropColumn('kapasitas_per_jam');
            }
            if (Schema::hasColumn('proses_produksis', 'btkl_id')) {
                $table->dropColumn('btkl_id');
            }
            if (Schema::hasColumn('proses_produksis', 'biaya_btkl_per_produk')) {
                $table->dropColumn('biaya_btkl_per_produk');
            }
        });

        Schema::table('proses_produksis', function (Blueprint $table) {
            // Add new columns to match BTKL form
            $table->decimal('tarif_per_produk', 15, 2)->default(0)->comment('Tarif BTKL per produk');
            $table->decimal('jumlah_pegawai', 8, 2)->default(0)->comment('Jumlah pegawai untuk proses ini');
            
            // Update existing tarif_btkl column
            $table->decimal('tarif_btkl', 15, 2)->default(0)->comment('Total tarif BTKL (jumlah_pegawai × tarif_per_produk)');
            
            // Add indexes for new columns
            $table->index('jumlah_pegawai');
            $table->index('tarif_per_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Remove new columns
            if (Schema::hasColumn('proses_produksis', 'tarif_per_produk')) {
                $table->dropColumn('tarif_per_produk');
            }
            if (Schema::hasColumn('proses_produksis', 'jumlah_pegawai')) {
                $table->dropColumn('jumlah_pegawai');
            }
        });

        Schema::table('proses_produksis', function (Blueprint $table) {
            // Restore old columns
            $table->string('satuan_btkl', 20)->default('jam')->comment('Satuan waktu (jam, menit, unit)');
            $table->integer('kapasitas_per_jam')->default(0)->comment('Kapasitas per jam (sync dari BTKL)');
            $table->foreignId('btkl_id')->nullable()->comment('Reference to BTKL');
            $table->decimal('biaya_btkl_per_produk', 15, 4)->default(0)->comment('Biaya BTKL per unit produk');
            
            // Restore original column type
            $table->decimal('tarif_btkl', 15, 2)->default(0)->comment('Tarif BTKL per satuan waktu (Rp/jam atau Rp/unit)');
            
            // Drop indexes - check if they exist first
            if (Schema::hasIndex('proses_produksis', ['jumlah_pegawai'])) {
                $table->dropIndex(['jumlah_pegawai']);
            }
            if (Schema::hasIndex('proses_produksis', ['tarif_per_produk'])) {
                $table->dropIndex(['tarif_per_produk']);
            }
        });
    }
};

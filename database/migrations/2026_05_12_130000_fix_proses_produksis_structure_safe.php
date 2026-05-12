<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Safe migration to update proses_produksis table structure
     */
    public function up(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Add new columns first
            if (!Schema::hasColumn('proses_produksis', 'tarif_per_produk')) {
                $table->decimal('tarif_per_produk', 15, 2)->default(0)->comment('Tarif BTKL per produk');
            }
            if (!Schema::hasColumn('proses_produksis', 'jumlah_pegawai')) {
                $table->decimal('jumlah_pegawai', 8, 2)->default(0)->comment('Jumlah pegawai untuk proses ini');
            }
        });

        // Update the tarif_btkl column comment and type
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->decimal('tarif_btkl', 15, 2)->default(0)->comment('Total tarif BTKL (jumlah_pegawai × tarif_per_produk)')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Remove new columns safely
            if (Schema::hasColumn('proses_produksis', 'tarif_per_produk')) {
                $table->dropColumn('tarif_per_produk');
            }
            if (Schema::hasColumn('proses_produksis', 'jumlah_pegawai')) {
                $table->dropColumn('jumlah_pegawai');
            }
        });

        // Restore original column type
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->decimal('tarif_btkl', 15, 2)->default(0)->comment('Tarif BTKL per satuan waktu (Rp/jam atau Rp/unit)')->change();
        });
    }
};

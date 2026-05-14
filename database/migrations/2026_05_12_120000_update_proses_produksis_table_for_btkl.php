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
        // Bagian 1: Hapus kolom lama jika ada
        Schema::table('proses_produksis', function (Blueprint $table) {
            if (Schema::hasColumn('proses_produksis', 'satuan_btkl')) {
                $table->dropColumn('satuan_btkl');
            }
            if (Schema::hasColumn('proses_produksis', 'kapasitas_per_jam')) {
                $table->dropColumn('kapasitas_per_jam');
            }
            if (Schema::hasColumn('proses_produksis', 'btkl_id')) {
                // Drop foreign key dulu jika ada sebelum drop column
                $table->dropColumn('btkl_id');
            }
            if (Schema::hasColumn('proses_produksis', 'biaya_btkl_per_produk')) {
                $table->dropColumn('biaya_btkl_per_produk');
            }
        });

        // Bagian 2: Tambah kolom baru & update kolom yang ada
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Cek sebelum tambah kolom 'tarif_per_produk'
            if (!Schema::hasColumn('proses_produksis', 'tarif_per_produk')) {
                $table->decimal('tarif_per_produk', 15, 2)->default(0)->comment('Tarif BTKL per produk');
                $table->index('tarif_per_produk'); // Langsung tambah index
            }

            // Cek sebelum tambah kolom 'jumlah_pegawai'
            if (!Schema::hasColumn('proses_produksis', 'jumlah_pegawai')) {
                $table->decimal('jumlah_pegawai', 8, 2)->default(0)->comment('Jumlah pegawai untuk proses ini');
                $table->index('jumlah_pegawai'); // Langsung tambah index
            }
            
            // Update kolom tarif_btkl (mengubah comment/struktur)
            if (Schema::hasColumn('proses_produksis', 'tarif_btkl')) {
                $table->decimal('tarif_btkl', 15, 2)->default(0)->comment('Total tarif BTKL (jumlah_pegawai × tarif_per_produk)')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Hapus kolom baru jika ada
            if (Schema::hasColumn('proses_produksis', 'tarif_per_produk')) {
                $table->dropColumn('tarif_per_produk');
            }
            if (Schema::hasColumn('proses_produksis', 'jumlah_pegawai')) {
                $table->dropColumn('jumlah_pegawai');
            }
        });

        Schema::table('proses_produksis', function (Blueprint $table) {
            // Kembalikan kolom lama
            if (!Schema::hasColumn('proses_produksis', 'satuan_btkl')) {
                $table->string('satuan_btkl', 20)->default('jam')->comment('Satuan waktu (jam, menit, unit)');
            }
            if (!Schema::hasColumn('proses_produksis', 'kapasitas_per_jam')) {
                $table->integer('kapasitas_per_jam')->default(0)->comment('Kapasitas per jam (sync dari BTKL)');
            }
            if (!Schema::hasColumn('proses_produksis', 'btkl_id')) {
                $table->foreignId('btkl_id')->nullable()->comment('Reference to BTKL');
            }
            if (!Schema::hasColumn('proses_produksis', 'biaya_btkl_per_produk')) {
                $table->decimal('biaya_btkl_per_produk', 15, 4)->default(0)->comment('Biaya BTKL per unit produk');
            }
            
            // Kembalikan tarif_btkl ke semula
            if (Schema::hasColumn('proses_produksis', 'tarif_btkl')) {
                $table->decimal('tarif_btkl', 15, 2)->default(0)->comment('Tarif BTKL per satuan waktu (Rp/jam atau Rp/unit)')->change();
            }
        });
    }
};
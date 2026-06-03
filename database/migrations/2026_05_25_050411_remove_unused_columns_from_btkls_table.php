<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menghapus kolom yang tidak digunakan lagi dari tabel btkls:
     * - tarif_per_jam (diganti dengan tarif_produk di tabel jabatans)
     * - satuan (tidak digunakan lagi)
     * - kapasitas_per_jam (tidak digunakan lagi)
     * 
     * Sekarang menggunakan pembebanan per produk dari tabel jabatans
     */
    public function up(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            // Hapus kolom tarif_per_jam jika ada
            if (Schema::hasColumn('btkls', 'tarif_per_jam')) {
                $table->dropColumn('tarif_per_jam');
            }
            
            // Hapus kolom tarif_btkl jika ada (rename dari tarif_per_jam)
            if (Schema::hasColumn('btkls', 'tarif_btkl')) {
                $table->dropColumn('tarif_btkl');
            }
            
            // Hapus kolom satuan jika ada
            if (Schema::hasColumn('btkls', 'satuan')) {
                $table->dropColumn('satuan');
            }
            
            // Hapus kolom kapasitas_per_jam jika ada
            if (Schema::hasColumn('btkls', 'kapasitas_per_jam')) {
                $table->dropColumn('kapasitas_per_jam');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            // Kembalikan kolom jika rollback
            $table->decimal('tarif_per_jam', 15, 2)->default(0)->after('jabatan_id');
            $table->enum('satuan', ['Jam', 'Unit', 'Batch'])->default('Jam')->after('tarif_per_jam');
            $table->integer('kapasitas_per_jam')->default(0)->after('satuan');
        });
    }
};

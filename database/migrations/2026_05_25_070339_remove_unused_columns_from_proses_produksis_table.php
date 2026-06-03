<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menghapus kolom yang tidak digunakan lagi dari tabel proses_produksis:
     * - tarif_btkl (tidak digunakan, tarif diambil dari jabatans.tarif_produk)
     * - satuan_btkl (tidak digunakan, sistem perproduk bukan per jam)
     * - kapasitas_per_jam (tidak digunakan, sistem perproduk)
     * - biaya_btkl_per_produk (tidak digunakan, dihitung dari tarif_per_produk × jumlah_pegawai)
     * 
     * Kolom yang tetap digunakan:
     * - tarif_per_produk (dari jabatans)
     * - jumlah_pegawai
     * - jabatan_id
     */
    public function up(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Hapus kolom tarif_btkl jika ada
            if (Schema::hasColumn('proses_produksis', 'tarif_btkl')) {
                $table->dropColumn('tarif_btkl');
            }
            
            // Hapus kolom satuan_btkl jika ada
            if (Schema::hasColumn('proses_produksis', 'satuan_btkl')) {
                $table->dropColumn('satuan_btkl');
            }
            
            // Hapus kolom kapasitas_per_jam jika ada
            if (Schema::hasColumn('proses_produksis', 'kapasitas_per_jam')) {
                $table->dropColumn('kapasitas_per_jam');
            }
            
            // Hapus kolom biaya_btkl_per_produk jika ada
            if (Schema::hasColumn('proses_produksis', 'biaya_btkl_per_produk')) {
                $table->dropColumn('biaya_btkl_per_produk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Kembalikan kolom jika rollback
            $table->decimal('tarif_btkl', 15, 2)->default(0)->comment('Tarif BTKL per satuan waktu')->after('deskripsi');
            $table->string('satuan_btkl', 20)->default('jam')->comment('Satuan waktu (jam, menit, unit)')->after('tarif_btkl');
            $table->decimal('kapasitas_per_jam', 15, 2)->default(0)->comment('Kapasitas produksi per jam')->after('btkl_id');
            $table->decimal('biaya_btkl_per_produk', 15, 2)->default(0)->comment('Biaya BTKL per produk')->after('kapasitas_per_jam');
        });
    }
};

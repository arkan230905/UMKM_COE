<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah kolom disusutkan
        Schema::table('kategori_asets', function (Blueprint $table) {
            $table->boolean('disusutkan')->default(true)->after('tarif_penyusutan')
                ->comment('Apakah kategori aset ini mengalami penyusutan');
        });

        // Update data kategori yang TIDAK disusutkan
        $kategoriTidakDisusutkan = [
            // Aset Lancar - TIDAK disusutkan
            'Kas',
            'Bank',
            'Setara Kas',
            'Kas dan Bank',
            'Piutang Usaha',
            'Piutang Lainnya',
            'Piutang Lain-lain',
            'Persediaan Bahan Baku',
            'Persediaan Barang Jadi',
            'Persediaan Barang Dagang',
            'Persediaan Barang Dalam Proses',
            'Perlengkapan',
            'Biaya Dibayar Dimuka',
            'Uang Muka',
            'Uang Muka (Prepaid)',
            'Investasi Jangka Pendek',
            
            // Aset Tetap - Tanah TIDAK disusutkan
            'Tanah',
            
            // Aset Tak Berwujud - TIDAK disusutkan (diamortisasi)
            'Hak Cipta',
            'Paten',
            'Lisensi',
            'Lisensi Software',
            'Merek Dagang',
            'Merek Dagang (Trademark)',
            'Goodwill',
            'Software dan Aplikasi',
            'Franchise',
            
            // Investasi Jangka Panjang - TIDAK disusutkan
            'Saham',
            'Saham Jangka Panjang',
            'Obligasi',
            'Obligasi Jangka Panjang',
            'Deposito Jangka Panjang',
            'Investasi Properti',
            'Penyertaan Pada Perusahaan Lain',
            
            // Aset Lain-lain - TIDAK disusutkan
            'Deposito yang Dibatasi',
            'Aset Pajak Tangguhan',
            'Piutang Jangka Panjang',
            'Jaminan / Deposit',
            'Aset Lain-lain',
        ];

        DB::table('kategori_asets')
            ->whereIn('nama', $kategoriTidakDisusutkan)
            ->update(['disusutkan' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_asets', function (Blueprint $table) {
            $table->dropColumn('disusutkan');
        });
    }
};

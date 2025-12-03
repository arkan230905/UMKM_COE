<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Hapus data lama
        DB::table('kategori_asets')->truncate();
        DB::table('jenis_asets')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Insert Jenis Aset Baru
        $jenisAsets = [
            ['id' => 1, 'nama' => 'Aset Lancar', 'deskripsi' => 'Aset yang dapat dikonversi menjadi kas dalam waktu kurang dari 1 tahun'],
            ['id' => 2, 'nama' => 'Aset Tidak Lancar / Aset Tetap', 'deskripsi' => 'Aset berwujud yang digunakan dalam operasi bisnis jangka panjang'],
            ['id' => 3, 'nama' => 'Aset Tak Berwujud', 'deskripsi' => 'Aset non-fisik yang memiliki nilai ekonomis'],
            ['id' => 4, 'nama' => 'Aset Investasi Jangka Panjang', 'deskripsi' => 'Investasi yang dimaksudkan untuk dimiliki lebih dari 1 tahun'],
            ['id' => 5, 'nama' => 'Aset Lain-Lain', 'deskripsi' => 'Aset yang tidak termasuk kategori utama'],
        ];
        
        foreach ($jenisAsets as $jenis) {
            DB::table('jenis_asets')->insert(array_merge($jenis, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        // Insert Kategori Aset Baru
        $kategoriAsets = [
            // 1. ASET LANCAR
            ['jenis_aset_id' => 1, 'nama' => 'Kas', 'kode' => 'AL-KAS', 'umur_ekonomis' => null, 'tarif_penyusutan' => null],
            ['jenis_aset_id' => 1, 'nama' => 'Bank', 'kode' => 'AL-BANK', 'umur_ekonomis' => null, 'tarif_penyusutan' => null],
            ['jenis_aset_id' => 1, 'nama' => 'Setara Kas', 'kode' => 'AL-SETKAS'],
            ['jenis_aset_id' => 1, 'nama' => 'Piutang Usaha', 'kode' => 'AL-PIUTANG'],
            ['jenis_aset_id' => 1, 'nama' => 'Piutang Lainnya', 'kode' => 'AL-PIUTLAIN'],
            ['jenis_aset_id' => 1, 'nama' => 'Persediaan Barang Dagang', 'kode' => 'AL-PERSDG'],
            ['jenis_aset_id' => 1, 'nama' => 'Persediaan Bahan Baku', 'kode' => 'AL-PERSBB'],
            ['jenis_aset_id' => 1, 'nama' => 'Persediaan Barang Jadi', 'kode' => 'AL-PERSBJ'],
            ['jenis_aset_id' => 1, 'nama' => 'Perlengkapan', 'kode' => 'AL-PERLENG'],
            ['jenis_aset_id' => 1, 'nama' => 'Uang Muka (Prepaid)', 'kode' => 'AL-PREPAID'],
            ['jenis_aset_id' => 1, 'nama' => 'Investasi Jangka Pendek', 'kode' => 'AL-INVJP'],
            
            // 2. ASET TIDAK LANCAR / ASET TETAP
            ['jenis_aset_id' => 2, 'nama' => 'Tanah', 'kode' => 'ATL-TANAH'],
            ['jenis_aset_id' => 2, 'nama' => 'Bangunan', 'kode' => 'ATL-BANGUNAN'],
            ['jenis_aset_id' => 2, 'nama' => 'Kendaraan', 'kode' => 'ATL-KENDARAAN'],
            ['jenis_aset_id' => 2, 'nama' => 'Mesin', 'kode' => 'ATL-MESIN'],
            ['jenis_aset_id' => 2, 'nama' => 'Peralatan Produksi', 'kode' => 'ATL-PERPROD'],
            ['jenis_aset_id' => 2, 'nama' => 'Peralatan Kantor', 'kode' => 'ATL-PERKANTOR'],
            ['jenis_aset_id' => 2, 'nama' => 'Furnitur & Inventaris', 'kode' => 'ATL-FURNITUR'],
            ['jenis_aset_id' => 2, 'nama' => 'Komputer & Perangkat IT', 'kode' => 'ATL-KOMPUTER'],
            ['jenis_aset_id' => 2, 'nama' => 'Aset Tetap Dalam Penyelesaian', 'kode' => 'ATL-ATDP'],
            
            // 3. ASET TAK BERWUJUD
            ['jenis_aset_id' => 3, 'nama' => 'Hak Cipta', 'kode' => 'ATB-HAKCIPTA'],
            ['jenis_aset_id' => 3, 'nama' => 'Paten', 'kode' => 'ATB-PATEN'],
            ['jenis_aset_id' => 3, 'nama' => 'Merek Dagang (Trademark)', 'kode' => 'ATB-MEREK'],
            ['jenis_aset_id' => 3, 'nama' => 'Lisensi Software', 'kode' => 'ATB-LISENSI'],
            ['jenis_aset_id' => 3, 'nama' => 'Goodwill', 'kode' => 'ATB-GOODWILL'],
            ['jenis_aset_id' => 3, 'nama' => 'Franchise', 'kode' => 'ATB-FRANCHISE'],
            
            // 4. ASET INVESTASI JANGKA PANJANG
            ['jenis_aset_id' => 4, 'nama' => 'Saham Jangka Panjang', 'kode' => 'AIJP-SAHAM'],
            ['jenis_aset_id' => 4, 'nama' => 'Obligasi Jangka Panjang', 'kode' => 'AIJP-OBLIGASI'],
            ['jenis_aset_id' => 4, 'nama' => 'Deposito Jangka Panjang', 'kode' => 'AIJP-DEPOSITO'],
            ['jenis_aset_id' => 4, 'nama' => 'Investasi Properti', 'kode' => 'AIJP-PROPERTI'],
            ['jenis_aset_id' => 4, 'nama' => 'Penyertaan Pada Perusahaan Lain', 'kode' => 'AIJP-PENYERTAAN'],
            
            // 5. ASET LAIN-LAIN
            ['jenis_aset_id' => 5, 'nama' => 'Jaminan / Deposit', 'kode' => 'ALL-JAMINAN'],
            ['jenis_aset_id' => 5, 'nama' => 'Aset Pajak Tangguhan', 'kode' => 'ALL-PAJAKTAN'],
            ['jenis_aset_id' => 5, 'nama' => 'Piutang Jangka Panjang', 'kode' => 'ALL-PIUTJP'],
        ];
        
        foreach ($kategoriAsets as $kategori) {
            DB::table('kategori_asets')->insert(array_merge($kategori, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        // Rollback: hapus data yang baru diinsert
        DB::table('kategori_asets')->truncate();
        DB::table('jenis_asets')->truncate();
    }
};

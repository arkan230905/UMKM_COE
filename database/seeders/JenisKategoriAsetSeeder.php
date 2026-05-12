<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisAset;
use App\Models\KategoriAset;

class JenisKategoriAsetSeeder extends Seeder
{
    public function run()
    {
        // 1️⃣ Aset Tetap (Fixed Assets / Aset Berwujud)
        $asetTetap = JenisAset::updateOrCreate(
            ['nama' => 'Aset Tetap'],
            [
                'nama' => 'Aset Tetap',
                'deskripsi' => 'Aset berwujud yang digunakan untuk operasional dan memiliki masa manfaat > 1 tahun'
            ]
        );

        // Kategori Aset Tetap - Mengalami Penyusutan
        $kategoriTetapDisusutkan = [
            ['nama' => 'Bangunan', 'kode' => 'BGN', 'umur_ekonomis' => 20, 'tarif_penyusutan' => 5.00, 'disusutkan' => true],
            ['nama' => 'Mesin', 'kode' => 'MSN', 'umur_ekonomis' => 8, 'tarif_penyusutan' => 12.50, 'disusutkan' => true],
            ['nama' => 'Kendaraan', 'kode' => 'KND', 'umur_ekonomis' => 5, 'tarif_penyusutan' => 20.00, 'disusutkan' => true],
            ['nama' => 'Peralatan Kantor', 'kode' => 'PKT', 'umur_ekonomis' => 4, 'tarif_penyusutan' => 25.00, 'disusutkan' => true],
            ['nama' => 'Peralatan Pabrik', 'kode' => 'PPB', 'umur_ekonomis' => 10, 'tarif_penyusutan' => 10.00, 'disusutkan' => true],
            ['nama' => 'Instalasi Listrik/Air', 'kode' => 'ILA', 'umur_ekonomis' => 15, 'tarif_penyusutan' => 6.67, 'disusutkan' => true],
        ];

        foreach ($kategoriTetapDisusutkan as $kategori) {
            KategoriAset::updateOrCreate(
                ['jenis_aset_id' => $asetTetap->id, 'nama' => $kategori['nama']],
                [
                    'jenis_aset_id' => $asetTetap->id,
                    'kode' => $kategori['kode'],
                    'nama' => $kategori['nama'],
                    'deskripsi' => 'Kategori aset tetap yang mengalami penyusutan',
                    'umur_ekonomis' => $kategori['umur_ekonomis'],
                    'tarif_penyusutan' => $kategori['tarif_penyusutan'],
                    'disusutkan' => $kategori['disusutkan']
                ]
            );
        }

        // Kategori Aset Tetap - Tidak Mengalami Penyusutan
        KategoriAset::updateOrCreate(
            ['jenis_aset_id' => $asetTetap->id, 'nama' => 'Tanah'],
            [
                'jenis_aset_id' => $asetTetap->id,
                'kode' => 'TNH',
                'nama' => 'Tanah',
                'deskripsi' => 'Tanah dianggap memiliki umur manfaat tidak terbatas, sehingga tidak disusutkan',
                'umur_ekonomis' => 0,
                'tarif_penyusutan' => 0.00,
                'disusutkan' => false
            ]
        );

        // 2️⃣ Aset Tidak Tetap / Aset Lancar
        $asetLancar = JenisAset::updateOrCreate(
            ['nama' => 'Aset Lancar'],
            [
                'nama' => 'Aset Lancar',
                'deskripsi' => 'Aset yang dapat dikonversi menjadi kas atau digunakan dalam waktu ≤ 1 tahun'
            ]
        );

        // Kategori Aset Lancar - Tidak Mengalami Penyusutan
        $kategoriLancar = [
            ['nama' => 'Kas dan Setara Kas', 'kode' => 'KAS'],
            ['nama' => 'Piutang Usaha', 'kode' => 'PUT'],
            ['nama' => 'Persediaan Barang', 'kode' => 'PSB'],
            ['nama' => 'Beban Dibayar di Muka', 'kode' => 'BDM'],
            ['nama' => 'Investasi Jangka Pendek', 'kode' => 'IJP']
        ];

        foreach ($kategoriLancar as $kategori) {
            KategoriAset::updateOrCreate(
                ['jenis_aset_id' => $asetLancar->id, 'nama' => $kategori['nama']],
                [
                    'jenis_aset_id' => $asetLancar->id,
                    'kode' => $kategori['kode'],
                    'nama' => $kategori['nama'],
                    'deskripsi' => 'Aset lancar tidak disusutkan, karena habis dipakai atau berputar cepat',
                    'umur_ekonomis' => 0,
                    'tarif_penyusutan' => 0.00,
                    'disusutkan' => false
                ]
            );
        }

        // 3️⃣ Aset Tidak Berwujud (Intangible Assets)
        $asetTidakBerwujud = JenisAset::updateOrCreate(
            ['nama' => 'Aset Tidak Berwujud'],
            [
                'nama' => 'Aset Tidak Berwujud',
                'deskripsi' => 'Aset tanpa bentuk fisik, tetapi memberi manfaat ekonomi'
            ]
        );

        // Kategori Aset Tidak Berwujud - Mengalami Amortisasi
        $kategoriTidakBerwujudAmortisasi = [
            ['nama' => 'Hak Paten', 'kode' => 'HPT', 'umur_ekonomis' => 20, 'tarif_penyusutan' => 5.00],
            ['nama' => 'Hak Cipta', 'kode' => 'HCP', 'umur_ekonomis' => 15, 'tarif_penyusutan' => 6.67],
            ['nama' => 'Lisensi', 'kode' => 'LIS', 'umur_ekonomis' => 10, 'tarif_penyusutan' => 10.00],
            ['nama' => 'Franchise', 'kode' => 'FRC', 'umur_ekonomis' => 10, 'tarif_penyusutan' => 10.00],
            ['nama' => 'Software', 'kode' => 'SFT', 'umur_ekonomis' => 3, 'tarif_penyusutan' => 33.33],
            ['nama' => 'Merek Dagang (Terbatas)', 'kode' => 'MDT', 'umur_ekonomis' => 10, 'tarif_penyusutan' => 10.00],
        ];

        foreach ($kategoriTidakBerwujudAmortisasi as $kategori) {
            KategoriAset::updateOrCreate(
                ['jenis_aset_id' => $asetTidakBerwujud->id, 'nama' => $kategori['nama']],
                [
                    'jenis_aset_id' => $asetTidakBerwujud->id,
                    'kode' => $kategori['kode'],
                    'nama' => $kategori['nama'],
                    'deskripsi' => 'Aset tidak berwujud dengan umur manfaat terbatas yang mengalami amortisasi',
                    'umur_ekonomis' => $kategori['umur_ekonomis'],
                    'tarif_penyusutan' => $kategori['tarif_penyusutan'],
                    'disusutkan' => true
                ]
            );
        }

        // Kategori Aset Tidak Berwujud - Tidak Mengalami Amortisasi
        $kategoriTidakBerwujudTanpaAmortisasi = [
            ['nama' => 'Goodwill', 'kode' => 'GDW'],
            ['nama' => 'Merek Dagang (Tidak Terbatas)', 'kode' => 'MDU']
        ];

        foreach ($kategoriTidakBerwujudTanpaAmortisasi as $kategori) {
            KategoriAset::updateOrCreate(
                ['jenis_aset_id' => $asetTidakBerwujud->id, 'nama' => $kategori['nama']],
                [
                    'jenis_aset_id' => $asetTidakBerwujud->id,
                    'kode' => $kategori['kode'],
                    'nama' => $kategori['nama'],
                    'deskripsi' => 'Aset tidak berwujud dengan umur manfaat tidak terbatas, wajib diuji impairment',
                    'umur_ekonomis' => 0,
                    'tarif_penyusutan' => 0.00,
                    'disusutkan' => false
                ]
            );
        }

        $this->command->info('Jenis dan Kategori Aset berhasil di-seed!');
    }
}
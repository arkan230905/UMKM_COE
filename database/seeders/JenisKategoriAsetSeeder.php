<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisAset;
use App\Models\KategoriAset;

class JenisKategoriAsetSeeder extends Seeder
{
    public function run()
    {
        // Only seed Aset Tetap (Fixed Assets / Aset Berwujud)
        // Aset Lancar and Aset Tidak Berwujud have been removed per business requirement
        
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

        $this->command->info('Jenis dan Kategori Aset (Aset Tetap saja) berhasil di-seed!');
    }
}
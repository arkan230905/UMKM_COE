<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satuan;
use App\Models\JenisAset;
use App\Models\KategoriAset;
use Illuminate\Support\Facades\DB;

class InitialSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder ini untuk setup awal Satuan, Jenis Aset, dan Kategori Aset untuk user baru yang daftar
     * COA sudah dipindahkan ke CoaTemplateSeeder
     */
    public function run(): void
    {
        $this->seedSatuan();
        $this->seedJenisAset();
        $this->seedKategoriAset();
    }

    private function seedSatuan(): void
    {
        $satuans = [
            ['kode' => 'G',     'nama' => 'Gram',         'kategori' => 'berat',   'faktor_ke_dasar' => 1],
            ['kode' => 'ONS',   'nama' => 'Ons',          'kategori' => 'berat',   'faktor_ke_dasar' => 100],
            ['kode' => 'KG',    'nama' => 'Kilogram',     'kategori' => 'berat',   'faktor_ke_dasar' => 1000],
            ['kode' => 'ML',    'nama' => 'Mililiter',    'kategori' => 'volume',  'faktor_ke_dasar' => 1],
            ['kode' => 'LTR',   'nama' => 'Liter',        'kategori' => 'volume',  'faktor_ke_dasar' => 1000],
            ['kode' => 'GL',    'nama' => 'Galon',        'kategori' => 'volume',  'faktor_ke_dasar' => 19000],
            ['kode' => 'SDT',   'nama' => 'Sendok Teh',   'kategori' => 'volume',  'faktor_ke_dasar' => 5],
            ['kode' => 'SDM',   'nama' => 'Sendok Makan', 'kategori' => 'volume',  'faktor_ke_dasar' => 15],
            ['kode' => 'PCS',   'nama' => 'Pieces',       'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'PTG',   'nama' => 'Potong',       'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'EKOR',  'nama' => 'Ekor',         'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'BNGKS', 'nama' => 'Bungkus',      'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'TBG',   'nama' => 'Tabung',       'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'SNG',   'nama' => 'Siung',        'kategori' => 'jumlah',  'faktor_ke_dasar' => 1],
            ['kode' => 'WATT',  'nama' => 'Watt',         'kategori' => 'daya',    'faktor_ke_dasar' => 1],
        ];

        foreach ($satuans as $satuan) {
            Satuan::updateOrCreate(
                ['kode' => $satuan['kode']],
                $satuan
            );
        }

        $this->command->info('✓ Satuan berhasil di-seed! Total: ' . count($satuans) . ' satuan.');
    }

    private function seedJenisAset(): void
    {
        $jenisAsets = [
            [
                'nama' => 'Aset Tetap',
                'deskripsi' => 'Aset yang digunakan dalam operasional jangka panjang dan memiliki umur manfaat lebih dari satu tahun'
            ],
            [
                'nama' => 'Aset Lancar',
                'deskripsi' => 'Aset yang dapat dikonversi menjadi kas dalam waktu singkat (kurang dari satu tahun)'
            ],
            [
                'nama' => 'Aset Tidak Berwujud',
                'deskripsi' => 'Aset yang tidak memiliki bentuk fisik seperti hak paten, merek dagang, dan goodwill'
            ],
        ];

        foreach ($jenisAsets as $jenis) {
            JenisAset::updateOrCreate(
                ['nama' => $jenis['nama']],
                $jenis
            );
        }

        $this->command->info('✓ Jenis Aset berhasil di-seed! Total: ' . count($jenisAsets) . ' jenis.');
    }

    private function seedKategoriAset(): void
    {
        // Ambil ID jenis aset
        $asetTetap = JenisAset::where('nama', 'Aset Tetap')->first();
        $asetLancar = JenisAset::where('nama', 'Aset Lancar')->first();
        $asetTidakBerwujud = JenisAset::where('nama', 'Aset Tidak Berwujud')->first();

        if (!$asetTetap || !$asetLancar || !$asetTidakBerwujud) {
            $this->command->error('✗ Jenis Aset belum ada! Jalankan seedJenisAset terlebih dahulu.');
            return;
        }

        $kategoriAsets = [
            // Aset Tetap
            [
                'jenis_aset_id' => $asetTetap->id,
                'kode' => 'TNH',
                'nama' => 'Tanah',
                'deskripsi' => 'Tanah yang dimiliki perusahaan',
                'umur_ekonomis' => 0, // Tanah tidak disusutkan
                'tarif_penyusutan' => 0,
                'disusutkan' => false,
            ],
            [
                'jenis_aset_id' => $asetTetap->id,
                'kode' => 'BGN',
                'nama' => 'Bangunan',
                'deskripsi' => 'Gedung dan bangunan',
                'umur_ekonomis' => 20,
                'tarif_penyusutan' => 5, // 100% / 20 tahun
                'disusutkan' => true,
            ],
            [
                'jenis_aset_id' => $asetTetap->id,
                'kode' => 'KND',
                'nama' => 'Kendaraan',
                'deskripsi' => 'Kendaraan operasional',
                'umur_ekonomis' => 8,
                'tarif_penyusutan' => 12.5, // 100% / 8 tahun
                'disusutkan' => true,
            ],
            [
                'jenis_aset_id' => $asetTetap->id,
                'kode' => 'PRL',
                'nama' => 'Peralatan',
                'deskripsi' => 'Peralatan produksi dan operasional',
                'umur_ekonomis' => 5,
                'tarif_penyusutan' => 20, // 100% / 5 tahun
                'disusutkan' => true,
            ],
            [
                'jenis_aset_id' => $asetTetap->id,
                'kode' => 'MSN',
                'nama' => 'Mesin',
                'deskripsi' => 'Mesin produksi',
                'umur_ekonomis' => 8,
                'tarif_penyusutan' => 12.5, // 100% / 8 tahun
                'disusutkan' => true,
            ],
            [
                'jenis_aset_id' => $asetTetap->id,
                'kode' => 'INV',
                'nama' => 'Inventaris Kantor',
                'deskripsi' => 'Furniture dan perlengkapan kantor',
                'umur_ekonomis' => 4,
                'tarif_penyusutan' => 25, // 100% / 4 tahun
                'disusutkan' => true,
            ],
            
            // Aset Lancar
            [
                'jenis_aset_id' => $asetLancar->id,
                'kode' => 'PSD',
                'nama' => 'Persediaan',
                'deskripsi' => 'Persediaan barang dagangan',
                'umur_ekonomis' => 0,
                'tarif_penyusutan' => 0,
                'disusutkan' => false,
            ],
            [
                'jenis_aset_id' => $asetLancar->id,
                'kode' => 'PTG',
                'nama' => 'Piutang',
                'deskripsi' => 'Piutang usaha',
                'umur_ekonomis' => 0,
                'tarif_penyusutan' => 0,
                'disusutkan' => false,
            ],
            
            // Aset Tidak Berwujud
            [
                'jenis_aset_id' => $asetTidakBerwujud->id,
                'kode' => 'GDW',
                'nama' => 'Goodwill',
                'deskripsi' => 'Nilai goodwill perusahaan',
                'umur_ekonomis' => 5,
                'tarif_penyusutan' => 20,
                'disusutkan' => true,
            ],
            [
                'jenis_aset_id' => $asetTidakBerwujud->id,
                'kode' => 'PTN',
                'nama' => 'Paten',
                'deskripsi' => 'Hak paten dan kekayaan intelektual',
                'umur_ekonomis' => 10,
                'tarif_penyusutan' => 10,
                'disusutkan' => true,
            ],
        ];

        foreach ($kategoriAsets as $kategori) {
            KategoriAset::updateOrCreate(
                ['kode' => $kategori['kode']],
                $kategori
            );
        }

        $this->command->info('✓ Kategori Aset berhasil di-seed! Total: ' . count($kategoriAsets) . ' kategori.');
    }
}

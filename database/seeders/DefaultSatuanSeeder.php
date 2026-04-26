<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Satuan;

class DefaultSatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat satuan default untuk setiap perusahaan baru dengan multi-tenant isolation
     */
    public function run(int $userId): void
    {
        $defaultSatuans = [
            ['kode' => 'ONS', 'nama' => 'Ons'],
            ['kode' => 'KG', 'nama' => 'Kilogram'],
            ['kode' => 'ML', 'nama' => 'Mililiter'],
            ['kode' => 'G', 'nama' => 'Gram'],
            ['kode' => 'LTR', 'nama' => 'Liter'],
            ['kode' => 'PTG', 'nama' => 'Potong'],
            ['kode' => 'EKOR', 'nama' => 'Ekor'],
            ['kode' => 'SDT', 'nama' => 'Sendok Teh'],
            ['kode' => 'SDM', 'nama' => 'Sendok Makan'],
            ['kode' => 'PCS', 'nama' => 'Pieces'],
            ['kode' => 'BNGKS', 'nama' => 'Bungkus'],
            ['kode' => 'CUP', 'nama' => 'Cup'],
            ['kode' => 'GL', 'nama' => 'Galon'],
            ['kode' => 'TBG', 'nama' => 'Tabung'],
            ['kode' => 'SNG', 'nama' => 'Siung'],
        ];

        foreach ($defaultSatuans as $satuanData) {
            Satuan::firstOrCreate(
                [
                    'user_id' => $userId,
                    'kode' => $satuanData['kode']
                ],
                [
                    'nama' => $satuanData['nama'],
                    'tipe' => $this->getTipeSatuan($satuanData['kode']),
                    'kategori' => $this->getKategoriSatuan($satuanData['kode']),
                    'is_dasar' => $this->isSatuanDasar($satuanData['kode']),
                    'is_active' => true,
                    'nilai_konversi' => $this->getNilaiKonversi($satuanData['kode']),
                    'faktor_ke_dasar' => $this->getFaktorKeDasar($satuanData['kode']),
                ]
            );
        }
    }

    /**
     * Create default satuan for specific user (alias for backward compatibility)
     */
    public static function createForUser($userId): void
    {
        $seeder = new self();
        $seeder->run($userId);
    }

    /**
     * Menentukan tipe satuan berdasarkan kode
     */
    private function getTipeSatuan($kode): string
    {
        $weightUnits = ['ONS', 'KG', 'G'];
        $volumeUnits = ['ML', 'LTR', 'CUP', 'GL'];
        $unitUnits = ['PTG', 'EKOR', 'PCS', 'BNGKS', 'TBG', 'SNG'];
        $spoonUnits = ['SDT', 'SDM'];

        if (in_array($kode, $weightUnits)) return 'weight';
        if (in_array($kode, $volumeUnits)) return 'volume';
        if (in_array($kode, $unitUnits)) return 'unit';
        if (in_array($kode, $spoonUnits)) return 'volume';
        
        return 'unit';
    }

    /**
     * Menentukan kategori satuan
     */
    private function getKategoriSatuan($kode): string
    {
        $weightUnits = ['ONS', 'KG', 'G'];
        $volumeUnits = ['ML', 'LTR', 'CUP', 'GL', 'SDT', 'SDM'];
        $countUnits = ['PTG', 'EKOR', 'PCS', 'BNGKS', 'TBG', 'SNG'];

        if (in_array($kode, $weightUnits)) return 'berat';
        if (in_array($kode, $volumeUnits)) return 'volume';
        if (in_array($kode, $countUnits)) return 'jumlah';
        
        return 'jumlah';
    }

    /**
     * Menentukan apakah satuan dasar
     */
    private function isSatuanDasar($kode): bool
    {
        $dasarUnits = ['KG', 'LTR', 'PCS'];
        return in_array($kode, $dasarUnits);
    }

    /**
     * Menentukan nilai konversi ke satuan dasar
     */
    private function getNilaiKonversi($kode): float
    {
        $konversi = [
            'ONS' => 0.025,      // 1 ons = 0.025 kg
            'KG' => 1.0,         // 1 kg = 1 kg (dasar)
            'G' => 0.001,        // 1 gram = 0.001 kg
            'ML' => 0.001,       // 1 ml = 0.001 liter
            'LTR' => 1.0,        // 1 liter = 1 liter (dasar)
            'CUP' => 0.24,       // 1 cup = 0.24 liter
            'GL' => 3.785,       // 1 galon = 3.785 liter
            'SDT' => 0.005,      // 1 sendok teh = 5 ml = 0.005 liter
            'SDM' => 0.015,      // 1 sendok makan = 15 ml = 0.015 liter
        ];

        return $konversi[$kode] ?? 1.0;
    }

    /**
     * Menentukan faktor ke satuan dasar
     */
    private function getFaktorKeDasar($kode): float
    {
        return $this->getNilaiKonversi($kode);
    }
}

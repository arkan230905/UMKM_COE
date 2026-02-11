<?php

namespace App\Services;

use App\Models\Satuan;
use App\Models\SatuanGrup;

class SatuanKonversiService
{
    /**
     * Konversi satuan dari satu satuan ke satuan lain
     */
    public static function konversi($jumlah, $dariSatuanId, $keSatuanId)
    {
        $dariSatuan = Satuan::find($dariSatuanId);
        $keSatuan = Satuan::find($keSatuanId);

        if (!$dariSatuan || !$keSatuan) {
            throw new \Exception("Satuan tidak ditemukan");
        }

        return $dariSatuan->konversiKe($jumlah, $keSatuan);
    }

    /**
     * Konversi ke satuan dasar
     */
    public static function konversiKeDasar($jumlah, $satuanId)
    {
        $satuan = Satuan::find($satuanId);
        if (!$satuan) {
            throw new \Exception("Satuan tidak ditemukan");
        }

        return $satuan->konversiKeDasar($jumlah);
    }

    /**
     * Dapatkan semua satuan dalam grup yang sama
     */
    public static function getSatuanSeGrup($satuanId)
    {
        $satuan = Satuan::find($satuanId);
        if (!$satuan) {
            return collect([]);
        }

        return $satuan->getSatuanSeGrup();
    }

    /**
     * Buat grup satuan baru dengan satuan dasar
     */
    public static function buatGrup($namaGrup, $kodeGrup, $namaSatuanDasar, $kodeSatuanDasar, $tipe = 'unit')
    {
        // Buat grup
        $grup = SatuanGrup::create([
            'nama' => $namaGrup,
            'kode' => $kodeGrup,
            'keterangan' => "Grup satuan {$namaGrup}"
        ]);

        // Buat satuan dasar
        $satuanDasar = Satuan::create([
            'kode' => $kodeSatuanDasar,
            'nama' => $namaSatuanDasar,
            'tipe' => $tipe,
            'satuan_grup_id' => $grup->id,
            'satuan_dasar_id' => null,
            'nilai_konversi' => 1.000000,
            'is_dasar' => true,
            'keterangan' => "Satuan dasar untuk {$namaGrup}"
        ]);

        return [
            'grup' => $grup,
            'satuan_dasar' => $satuanDasar
        ];
    }

    /**
     * Tambahkan satuan turunan ke grup yang ada
     */
    public static function tambahSatuanTurunan($namaSatuan, $kodeSatuan, $satuanDasarId, $nilaiKonversi, $keterangan = null)
    {
        $satuanDasar = Satuan::find($satuanDasarId);
        if (!$satuanDasar || !$satuanDasar->is_dasar) {
            throw new \Exception("Satuan dasar tidak valid");
        }

        return Satuan::create([
            'kode' => $kodeSatuan,
            'nama' => $namaSatuan,
            'tipe' => $satuanDasar->tipe,
            'satuan_grup_id' => $satuanDasar->satuan_grup_id,
            'satuan_dasar_id' => $satuanDasar->id,
            'nilai_konversi' => $nilaiKonversi,
            'is_dasar' => false,
            'keterangan' => $keterangan
        ]);
    }

    /**
     * Validasi konversi antar satuan
     */
    public static function validasiKonversi($dariSatuanId, $keSatuanId)
    {
        $dariSatuan = Satuan::find($dariSatuanId);
        $keSatuan = Satuan::find($keSatuanId);

        if (!$dariSatuan || !$keSatuan) {
            return ['valid' => false, 'pesan' => 'Satuan tidak ditemukan'];
        }

        if ($dariSatuan->satuan_grup_id !== $keSatuan->satuan_grup_id) {
            return ['valid' => false, 'pesan' => 'Satuan tidak dalam grup yang sama'];
        }

        return ['valid' => true, 'pesan' => 'Konversi valid'];
    }

    /**
     * Format tampilan konversi
     */
    public static function formatKonversi($jumlah, $dariSatuanId, $keSatuanId)
    {
        try {
            $hasil = self::konversi($jumlah, $dariSatuanId, $keSatuanId);
            $dariSatuan = Satuan::find($dariSatuanId);
            $keSatuan = Satuan::find($keSatuanId);

            return [
                'hasil' => $hasil,
                'format' => "{$jumlah} {$dariSatuan->nama} = {$hasil} {$keSatuan->nama}",
                'detail' => [
                    'dari' => [
                        'jumlah' => $jumlah,
                        'satuan' => $dariSatuan->nama
                    ],
                    'ke' => [
                        'jumlah' => $hasil,
                        'satuan' => $keSatuan->nama
                    ]
                ]
            ];
        } catch (\Exception $e) {
            return [
                'hasil' => null,
                'format' => 'Error: ' . $e->getMessage(),
                'detail' => null
            ];
        }
    }

    /**
     * Inisialisasi grup satuan default
     */
    public static function inisialisasiGrupDefault()
    {
        $grupDefaults = [
            [
                'nama' => 'Berat',
                'kode' => 'WEIGHT',
                'tipe' => 'weight',
                'satuan_dasar' => ['Kilogram', 'KG'],
                'turunan' => [
                    ['Gram', 'GR', 0.001000], // 1 GR = 0.001 KG
                    ['Miligram', 'MG', 0.000001], // 1 MG = 0.000001 KG
                ]
            ],
            [
                'nama' => 'Volume',
                'kode' => 'VOLUME',
                'tipe' => 'volume',
                'satuan_dasar' => ['Liter', 'L'],
                'turunan' => [
                    ['Mililiter', 'ML', 0.001000], // 1 ML = 0.001 L
                ]
            ],
            [
                'nama' => 'Unit',
                'kode' => 'UNIT',
                'tipe' => 'unit',
                'satuan_dasar' => ['Pieces', 'PCS'],
                'turunan' => [
                    ['Box', 'BOX', 10.000000], // 1 BOX = 10 PCS
                    ['Pack', 'PACK', 5.000000], // 1 PACK = 5 PCS
                ]
            ]
        ];

        foreach ($grupDefaults as $grupData) {
            // Cek apakah grup sudah ada
            $grup = SatuanGrup::where('kode', $grupData['kode'])->first();
            if (!$grup) {
                $result = self::buatGrup(
                    $grupData['nama'],
                    $grupData['kode'],
                    $grupData['satuan_dasar'][0],
                    $grupData['satuan_dasar'][1],
                    $grupData['tipe']
                );
                $grup = $result['grup'];
                $satuanDasar = $result['satuan_dasar'];

                // Tambahkan satuan turunan
                foreach ($grupData['turunan'] as $turunan) {
                    self::tambahSatuanTurunan(
                        $turunan[0],
                        $turunan[1],
                        $satuanDasar->id,
                        $turunan[2]
                    );
                }
            }
        }
    }
}

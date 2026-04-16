<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BahanPendukungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data bahan pendukung sesuai dengan Excel
        $bahanData = [
            [
                'kode_bahan' => 'AIR001',
                'nama_bahan' => 'Air',
                'kategori' => 'air',
                'satuan_utama' => 'Liter',
                'harga_satuan' => 1000,
                'stok' => 400,
                'stok_minimum' => 5,
                'deskripsi' => 'Air Mineral',
                'coa_pembelian' => 'BOP-Air (531)',
                'coa_persediaan' => 'Pers. Bahan Pendukung Air (1150)',
                'coa_hpp' => 'BOP-Air (531)',
                'sub_satuan' => [
                    ['satuan' => 'Kg', 'konversi' => 1, 'harga_per_unit' => 1000], // Rp 1.000/Kg
                    ['satuan' => 'Ml', 'konversi' => 1000, 'harga_per_unit' => 1], // Rp 1/Ml
                    ['satuan' => 'Galon', 'konversi' => 0.067, 'harga_per_unit' => 15000] // Rp 15.000/Galon
                ],
                'konversi' => [
                    ['satuan' => 'Kg', 'nilai' => 1],
                    ['satuan' => 'Ml', 'nilai' => 1000],
                    ['satuan' => 'Galon', 'nilai' => 0.067]
                ]
            ],

            [
                'kode_bahan' => 'TPG001',
                'nama_bahan' => 'Tepung Terigu',
                'kategori' => 'bumbu',
                'satuan_utama' => 'Bungkus',
                'harga_satuan' => 50000,
                'stok' => 400,
                'stok_minimum' => 5,
                'deskripsi' => 'Perbumbuan',
                'coa_pembelian' => 'BOP-Tepung Terigu (534)',
                'coa_persediaan' => 'Pers. Bahan Pendukung Tepung Terigu (1153)',
                'coa_hpp' => 'BOP-Tepung Terigu (534)',
                'sub_satuan' => [
                    ['satuan' => 'Sdt', 'konversi' => 12500, 'harga_per_unit' => 4], // Rp 4/Sdt
                    ['satuan' => 'Gram', 'konversi' => 500, 'harga_per_unit' => 100], // Rp 100/G
                    ['satuan' => 'Sdm', 'konversi' => 4.7, 'harga_per_unit' => 10638] // Rp 10.638/Sdm
                ],
                'konversi' => [
                    ['satuan' => 'Gram', 'nilai' => 500],
                    ['satuan' => 'Sdt', 'nilai' => 12500],
                    ['satuan' => 'Sdm', 'nilai' => 4.7]
                ]
            ],
            [
                'kode_bahan' => 'TPG002',
                'nama_bahan' => 'Tepung Maizena',
                'kategori' => 'bumbu',
                'satuan_utama' => 'Bungkus',
                'harga_satuan' => 50000,
                'stok' => 400,
                'stok_minimum' => 5,
                'deskripsi' => 'Perbumbuan',
                'coa_pembelian' => 'BOP-Tepung Maizena (535)',
                'coa_persediaan' => 'Pers. Bahan Pendukung Tepung Maizena (1154)',
                'coa_hpp' => 'BOP-Tepung Maizena (535)',
                'sub_satuan' => [
                    ['satuan' => 'Sdt', 'konversi' => 12500, 'harga_per_unit' => 4], // Rp 4/Sdt
                    ['satuan' => 'Gram', 'konversi' => 500, 'harga_per_unit' => 100], // Rp 100/G
                    ['satuan' => 'Sdm', 'konversi' => 4.7, 'harga_per_unit' => 10638] // Rp 10.638/Sdm
                ],
                'konversi' => [
                    ['satuan' => 'Gram', 'nilai' => 500],
                    ['satuan' => 'Sdt', 'nilai' => 12500],
                    ['satuan' => 'Sdm', 'nilai' => 4.7]
                ]
            ],
            [
                'kode_bahan' => 'LDA001',
                'nama_bahan' => 'Lada',
                'kategori' => 'bumbu',
                'satuan_utama' => 'Bungkus',
                'harga_satuan' => 15000,
                'stok' => 400,
                'stok_minimum' => 5,
                'deskripsi' => 'Perbumbuan',
                'coa_pembelian' => 'BOP- Lada (536)',
                'coa_persediaan' => 'Pers. Bahan Pendukung Lada (1155)',
                'coa_hpp' => 'BOP- Lada (536)',
                'sub_satuan' => [
                    ['satuan' => 'Sdt', 'konversi' => 5, 'harga_per_unit' => 3000], // Rp 3.000/Sdt
                    ['satuan' => 'Kg', 'konversi' => 0.01, 'harga_per_unit' => 1500000], // Rp 1.500.000/Kg
                    ['satuan' => 'Sdm', 'konversi' => 1.5, 'harga_per_unit' => 10000] // Rp 10.000/Sdm
                ],
                'konversi' => [
                    ['satuan' => 'Sdt', 'nilai' => 5],
                    ['satuan' => 'Kg', 'nilai' => 0.01],
                    ['satuan' => 'Sdm', 'nilai' => 1.5]
                ]
            ],

            [
                'kode_bahan' => 'BWG001',
                'nama_bahan' => 'Bubuk Bawang Putih',
                'kategori' => 'bumbu',
                'satuan_utama' => 'Kg',
                'harga_satuan' => 62000,
                'stok' => 400,
                'stok_minimum' => 5,
                'deskripsi' => 'Perbumbuan',
                'coa_pembelian' => 'BOP- Bubuk Bawang Putih (539)',
                'coa_persediaan' => 'Pers. Bahan Pendukung Bubuk Bawang Putih (1158)',
                'coa_hpp' => 'BOP- Bubuk Bawang Putih (539)',
                'sub_satuan' => [
                    ['satuan' => 'Gram', 'konversi' => 1000, 'harga_per_unit' => 62], // Rp 62/Gram
                    ['satuan' => 'Sdm', 'konversi' => 70, 'harga_per_unit' => 886], // Rp 886/Sdm
                    ['satuan' => 'Sdt', 'konversi' => 200, 'harga_per_unit' => 310] // Rp 310/Sdt
                ],
                'konversi' => [
                    ['satuan' => 'Gram', 'nilai' => 1000],
                    ['satuan' => 'Sdm', 'nilai' => 70],
                    ['satuan' => 'Sdt', 'nilai' => 200]
                ]
            ],
            [
                'kode_bahan' => 'KMS001',
                'nama_bahan' => 'Kemasan',
                'kategori' => 'lainnya',
                'satuan_utama' => 'Pcs',
                'harga_satuan' => 2000,
                'stok' => 400,
                'stok_minimum' => 5,
                'deskripsi' => 'Kemasan',
                'coa_pembelian' => 'BOP-Kemasan (540)',
                'coa_persediaan' => 'Pers. Bahan Pendukung Kemasan (1159)',
                'coa_hpp' => 'BOP-Kemasan (540)',
                'sub_satuan' => [
                    ['satuan' => 'Pcs', 'konversi' => 1, 'harga_per_unit' => 2000], // Rp 2.000/Pcs
                    ['satuan' => 'Pcs', 'konversi' => 1, 'harga_per_unit' => 2000], // Rp 2.000/Pcs
                    ['satuan' => 'Pcs', 'konversi' => 1, 'harga_per_unit' => 2000] // Rp 2.000/Pcs
                ],
                'konversi' => [
                    ['satuan' => 'Pcs', 'nilai' => 1]
                ]
            ],

        ];

        // Hapus data lama jika ada
        DB::table('bahan_konversi')->delete();
        DB::table('bahan_pendukungs')->delete();

        // Loop untuk setiap bahan
        foreach ($bahanData as $bahan) {
            // Ambil satuan_id berdasarkan nama satuan utama
            $satuanId = DB::table('satuans')
                ->where('nama', $bahan['satuan_utama'])
                ->value('id');

            // Jika satuan tidak ditemukan, buat satuan baru
            if (!$satuanId) {
                $satuanId = DB::table('satuans')->insertGetId([
                    'nama' => $bahan['satuan_utama'],
                    'kode' => strtoupper(substr($bahan['satuan_utama'], 0, 3)),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Siapkan data sub satuan dengan harga yang sudah ditetapkan
            $subSatuanData = [];
            for ($i = 0; $i < 3; $i++) {
                if (isset($bahan['sub_satuan'][$i])) {
                    $subSatuan = $bahan['sub_satuan'][$i];
                    
                    // Ambil atau buat satuan untuk sub satuan
                    $subSatuanId = DB::table('satuans')
                        ->where('nama', $subSatuan['satuan'])
                        ->value('id');
                    
                    if (!$subSatuanId) {
                        $subSatuanId = DB::table('satuans')->insertGetId([
                            'nama' => $subSatuan['satuan'],
                            'kode' => strtoupper(substr($subSatuan['satuan'], 0, 3)),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    
                    $subSatuanData['sub_satuan_' . ($i + 1) . '_id'] = $subSatuanId;
                    $subSatuanData['sub_satuan_' . ($i + 1) . '_konversi'] = $subSatuan['konversi'];
                    $subSatuanData['sub_satuan_' . ($i + 1) . '_nilai'] = $subSatuan['harga_per_unit'];
                }
            }

            // Ambil COA berdasarkan nama atau buat jika tidak ada
            $coaPembelianId = $this->getOrCreateCoa($bahan['coa_pembelian']);
            $coaPersediaanId = $this->getOrCreateCoa($bahan['coa_persediaan']);
            $coaHppId = $this->getOrCreateCoa($bahan['coa_hpp']);

            // Insert bahan pendukung dan dapatkan ID
            $insertData = array_merge([
                'kode_bahan' => $bahan['kode_bahan'],
                'nama_bahan' => $bahan['nama_bahan'],
                'satuan_id' => $satuanId,
                'harga_satuan' => $bahan['harga_satuan'],
                'stok' => $bahan['stok'],
                'stok_minimum' => $bahan['stok_minimum'],
                'deskripsi' => $bahan['deskripsi'],
                'kategori' => $bahan['kategori'],
                'coa_pembelian_id' => $coaPembelianId,
                'coa_persediaan_id' => $coaPersediaanId,
                'coa_hpp_id' => $coaHppId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ], $subSatuanData);

            $bahanId = DB::table('bahan_pendukungs')->insertGetId($insertData);

            // Insert konversi untuk bahan ini
            foreach ($bahan['konversi'] as $konversi) {
                // Ambil satuan_id untuk konversi
                $konversiSatuanId = DB::table('satuans')
                    ->where('nama', $konversi['satuan'])
                    ->value('id');

                // Jika satuan konversi tidak ditemukan, buat satuan baru
                if (!$konversiSatuanId) {
                    $konversiSatuanId = DB::table('satuans')->insertGetId([
                        'nama' => $konversi['satuan'],
                        'kode' => strtoupper(substr($konversi['satuan'], 0, 3)),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Insert konversi
                DB::table('bahan_konversi')->insert([
                    'bahan_id' => $bahanId,
                    'satuan_id' => $konversiSatuanId,
                    'nilai' => $konversi['nilai'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $this->command->info('Seeder BahanPendukung berhasil dijalankan!');
        $this->command->info('Total bahan pendukung: ' . count($bahanData) . ' (6 items)');
        
        // Hitung total konversi
        $totalKonversi = array_sum(array_map(function($bahan) {
            return count($bahan['konversi']);
        }, $bahanData));
        
        $this->command->info('Total konversi: ' . $totalKonversi);
    }

    /**
     * Get or create COA account
     */
    private function getOrCreateCoa($coaName)
    {
        // Extract kode akun from name (e.g., "BOP-Air (531)" -> "531")
        preg_match('/\((\d+)\)/', $coaName, $matches);
        $kodeAkun = $matches[1] ?? null;
        
        if (!$kodeAkun) {
            return null;
        }

        // Check if COA exists
        $existingCoa = DB::table('coas')->where('kode_akun', $kodeAkun)->first();
        
        if ($existingCoa) {
            return $kodeAkun;
        }

        // Create new COA if not exists
        $namaAkun = trim(preg_replace('/\(\d+\)/', '', $coaName));
        
        // Determine tipe_akun based on kode_akun
        $tipeAkun = 'beban'; // default
        if ($kodeAkun >= 1000 && $kodeAkun < 2000) {
            $tipeAkun = 'aset';
        }
        
        DB::table('coas')->insert([
            'kode_akun' => $kodeAkun,
            'nama_akun' => $namaAkun,
            'tipe_akun' => $tipeAkun,
            'saldo_normal' => $tipeAkun === 'aset' ? 'debit' : 'debit',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $kodeAkun;
    }
}
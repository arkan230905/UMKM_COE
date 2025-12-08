<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProsesProduksi;
use App\Models\KomponenBop;
use App\Models\ProsesBop;

class ProsesProduksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat data default untuk proses produksi dan komponen BOP
     */
    public function run(): void
    {
        // 1. Buat Komponen BOP (Biaya Overhead Pabrik)
        $komponenBops = [
            ['kode_komponen' => 'BOP-001', 'nama_komponen' => 'Listrik', 'satuan' => 'kWh', 'tarif_per_satuan' => 1500],
            ['kode_komponen' => 'BOP-002', 'nama_komponen' => 'Gas LPG', 'satuan' => 'kg', 'tarif_per_satuan' => 15000],
            ['kode_komponen' => 'BOP-003', 'nama_komponen' => 'Air PDAM', 'satuan' => 'm³', 'tarif_per_satuan' => 5000],
            ['kode_komponen' => 'BOP-004', 'nama_komponen' => 'Penyusutan Mesin', 'satuan' => 'jam', 'tarif_per_satuan' => 2000],
            ['kode_komponen' => 'BOP-005', 'nama_komponen' => 'Minyak Goreng', 'satuan' => 'liter', 'tarif_per_satuan' => 18000],
            ['kode_komponen' => 'BOP-006', 'nama_komponen' => 'Kemasan Plastik', 'satuan' => 'pcs', 'tarif_per_satuan' => 500],
            ['kode_komponen' => 'BOP-007', 'nama_komponen' => 'Label Stiker', 'satuan' => 'pcs', 'tarif_per_satuan' => 200],
        ];

        foreach ($komponenBops as $komponen) {
            KomponenBop::firstOrCreate(
                ['kode_komponen' => $komponen['kode_komponen']],
                $komponen
            );
        }

        // 2. Buat Proses Produksi dengan tarif BTKL
        $prosesProduksis = [
            [
                'kode_proses' => 'PRO-001',
                'nama_proses' => 'Persiapan Bahan',
                'deskripsi' => 'Proses persiapan dan pencucian bahan baku',
                'tarif_btkl' => 10000, // Rp 10.000/jam
                'satuan_btkl' => 'jam',
                'default_bops' => [
                    ['kode' => 'BOP-003', 'kuantitas' => 0.05], // Air 0.05 m³/jam
                ]
            ],
            [
                'kode_proses' => 'PRO-002',
                'nama_proses' => 'Pengolahan/Memasak',
                'deskripsi' => 'Proses memasak atau mengolah bahan',
                'tarif_btkl' => 15000, // Rp 15.000/jam
                'satuan_btkl' => 'jam',
                'default_bops' => [
                    ['kode' => 'BOP-002', 'kuantitas' => 0.5], // Gas 0.5 kg/jam
                    ['kode' => 'BOP-001', 'kuantitas' => 0.5], // Listrik 0.5 kWh/jam
                    ['kode' => 'BOP-004', 'kuantitas' => 1],   // Penyusutan 1 jam
                ]
            ],
            [
                'kode_proses' => 'PRO-003',
                'nama_proses' => 'Menggoreng',
                'deskripsi' => 'Proses menggoreng produk',
                'tarif_btkl' => 15000, // Rp 15.000/jam
                'satuan_btkl' => 'jam',
                'default_bops' => [
                    ['kode' => 'BOP-002', 'kuantitas' => 1],   // Gas 1 kg/jam
                    ['kode' => 'BOP-005', 'kuantitas' => 0.2], // Minyak 0.2 liter/jam
                    ['kode' => 'BOP-004', 'kuantitas' => 1],   // Penyusutan 1 jam
                ]
            ],
            [
                'kode_proses' => 'PRO-004',
                'nama_proses' => 'Membumbui',
                'deskripsi' => 'Proses pemberian bumbu pada produk',
                'tarif_btkl' => 12000, // Rp 12.000/jam
                'satuan_btkl' => 'jam',
                'default_bops' => [
                    ['kode' => 'BOP-001', 'kuantitas' => 0.3], // Listrik 0.3 kWh/jam
                ]
            ],
            [
                'kode_proses' => 'PRO-005',
                'nama_proses' => 'Pengemasan',
                'deskripsi' => 'Proses pengemasan produk jadi',
                'tarif_btkl' => 10000, // Rp 10.000/jam
                'satuan_btkl' => 'jam',
                'default_bops' => [
                    ['kode' => 'BOP-006', 'kuantitas' => 10], // Kemasan 10 pcs/jam
                    ['kode' => 'BOP-007', 'kuantitas' => 10], // Label 10 pcs/jam
                ]
            ],
            [
                'kode_proses' => 'PRO-006',
                'nama_proses' => 'Quality Control',
                'deskripsi' => 'Proses pengecekan kualitas produk',
                'tarif_btkl' => 12000, // Rp 12.000/jam
                'satuan_btkl' => 'jam',
                'default_bops' => []
            ],
        ];

        foreach ($prosesProduksis as $prosesData) {
            $defaultBops = $prosesData['default_bops'];
            unset($prosesData['default_bops']);
            
            $proses = ProsesProduksi::firstOrCreate(
                ['kode_proses' => $prosesData['kode_proses']],
                $prosesData
            );

            // Assign default BOP ke proses
            foreach ($defaultBops as $bopData) {
                $komponen = KomponenBop::where('kode_komponen', $bopData['kode'])->first();
                if ($komponen) {
                    ProsesBop::firstOrCreate(
                        [
                            'proses_produksi_id' => $proses->id,
                            'komponen_bop_id' => $komponen->id
                        ],
                        ['kuantitas_default' => $bopData['kuantitas']]
                    );
                }
            }
        }

        $this->command->info('✅ Proses Produksi dan Komponen BOP berhasil dibuat!');
    }
}

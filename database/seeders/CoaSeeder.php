<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;

class CoaSeeder extends Seeder
{
    public function run(): void
    {
        $coas = [
            // AKTIVA LANCAR (11xx)
            [
                'kode_akun' => '1101',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Kas Kecil',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Kas kecil untuk keperluan operasional harian'
            ],
            [
                'kode_akun' => '1102',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Kas di Bank',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Saldo rekening bank utama perusahaan'
            ],
            [
                'kode_akun' => '1103',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Piutang Usaha',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Piutang kepada pelanggan'
            ],
            [
                'kode_akun' => '1104',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Persediaan Bahan Baku',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Persediaan bahan baku produksi'
            ],
            [
                'kode_akun' => '1105',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Persediaan Barang Dalam Proses',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Barang yang masih dalam proses produksi'
            ],
            [
                'kode_akun' => '1106',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Persediaan Barang Jadi',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Barang jadi siap dijual'
            ],

            // AKTIVA TETAP (12xx)
            [
                'kode_akun' => '1201',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Tanah',
                'kategori_akun' => 'Aktiva Tetap',
                'is_akun_header' => true,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Tanah milik perusahaan'
            ],
            [
                'kode_akun' => '1202',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Gedung',
                'kategori_akun' => 'Aktiva Tetap',
                'is_akun_header' => true,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Gedung dan bangunan'
            ],
            [
                'kode_akun' => '120201',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Akumulasi Penyusutan Gedung',
                'kategori_akun' => 'Aktiva Tetap',
                'is_akun_header' => false,
                'kode_induk' => '1202',
                'saldo_normal' => 'kredit',
                'keterangan' => 'Akumulasi penyusutan gedung'
            ],
            [
                'kode_akun' => '1203',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Kendaraan',
                'kategori_akun' => 'Aktiva Tetap',
                'is_akun_header' => true,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Kendaraan operasional perusahaan'
            ],
            [
                'kode_akun' => '120301',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Akumulasi Penyusutan Kendaraan',
                'kategori_akun' => 'Aktiva Tetap',
                'is_akun_header' => false,
                'kode_induk' => '1203',
                'saldo_normal' => 'kredit',
                'keterangan' => 'Akumulasi penyusutan kendaraan'
            ],

            // KEWAJIBAN (2xxx)
            [
                'kode_akun' => '2101',
                'tipe_akun' => 'Liability',
                'nama_akun' => 'Hutang Usaha',
                'kategori_akun' => 'Kewajiban Jangka Pendek',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Hutang kepada supplier'
            ],
            [
                'kode_akun' => '2102',
                'tipe_akun' => 'Liability',
                'nama_akun' => 'Hutang Pajak',
                'kategori_akun' => 'Kewajiban Jangka Pendek',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Hutang pajak yang belum disetor'
            ],

            // MODAL (3xxx)
            [
                'kode_akun' => '3101',
                'tipe_akun' => 'Equity',
                'nama_akun' => 'Modal Saham',
                'kategori_akun' => 'Modal',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Modal disetor pemegang saham'
            ],
            [
                'kode_akun' => '3102',
                'tipe_akun' => 'Equity',
                'nama_akun' => 'Laba Ditahan',
                'kategori_akun' => 'Modal',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Akumulasi laba yang ditahan'
            ],

            // PENDAPATAN (4xxx)
            [
                'kode_akun' => '4101',
                'tipe_akun' => 'Revenue',
                'nama_akun' => 'Penjualan Produk',
                'kategori_akun' => 'Pendapatan Usaha',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Pendapatan dari penjualan produk'
            ],

            // BEBAN (5xxx)
            [
                'kode_akun' => '5101',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Beban Gaji Karyawan',
                'kategori_akun' => 'Beban Operasional',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Beban gaji dan tunjangan karyawan'
            ],
            [
                'kode_akun' => '5102',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Beban Listrik dan Air',
                'kategori_akun' => 'Beban Operasional',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Beban listrik dan air kantor'
            ],
            [
                'kode_akun' => '5201',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Bahan Baku',
                'kategori_akun' => 'Beban Produksi',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Bahan baku produksi'
            ]
        ];

        // Insert or update COA data
        foreach ($coas as $coa) {
            Coa::updateOrCreate(['kode_akun' => $coa['kode_akun']], $coa);
        }
    }
}

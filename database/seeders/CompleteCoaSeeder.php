<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;

class CompleteCoaSeeder extends Seeder
{
    /**
     * Menambahkan akun-akun COA yang masih kurang untuk sistem akuntansi yang lengkap
     */
    public function run(): void
    {
        $additionalCoas = [
            // AKTIVA LANCAR - Kas & Bank (Kode 4 digit)
            [
                'kode_akun' => '1102',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Kas di Bank',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
                'keterangan' => 'Saldo kas di rekening bank'
            ],
            [
                'kode_akun' => '1103',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Piutang Usaha',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'saldo_awal' => 0,
                'keterangan' => 'Piutang dari penjualan kredit'
            ],
            
            // AKTIVA LANCAR - Persediaan (Kode 3 digit untuk backward compatibility)
            [
                'kode_akun' => '121',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Persediaan Bahan Baku',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Persediaan bahan baku produksi (kode lama)'
            ],
            [
                'kode_akun' => '122',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Persediaan Barang Dalam Proses (WIP)',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Barang yang masih dalam proses produksi (kode lama)'
            ],
            [
                'kode_akun' => '123',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Persediaan Barang Jadi',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Barang jadi siap dijual (kode lama)'
            ],
            [
                'kode_akun' => '124',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Akumulasi Penyusutan',
                'kategori_akun' => 'Aktiva Tetap',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Akumulasi penyusutan aset tetap (kode lama)'
            ],
            
            // AKTIVA LANCAR - Persediaan (Kode 4 digit)
            [
                'kode_akun' => '1107',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Persediaan Barang Jadi',
                'kategori_akun' => 'Aktiva Lancar',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Persediaan barang jadi siap dijual'
            ],

            // AKTIVA TETAP - Akumulasi Penyusutan
            [
                'kode_akun' => '1204',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Peralatan dan Mesin',
                'kategori_akun' => 'Aktiva Tetap',
                'is_akun_header' => true,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Peralatan dan mesin produksi'
            ],
            [
                'kode_akun' => '120401',
                'tipe_akun' => 'Asset',
                'nama_akun' => 'Akumulasi Penyusutan Peralatan',
                'kategori_akun' => 'Aktiva Tetap',
                'is_akun_header' => false,
                'kode_induk' => '1204',
                'saldo_normal' => 'kredit',
                'keterangan' => 'Akumulasi penyusutan peralatan dan mesin'
            ],

            // KEWAJIBAN (Kode 3 digit untuk backward compatibility)
            [
                'kode_akun' => '201',
                'tipe_akun' => 'Liability',
                'nama_akun' => 'Hutang Usaha',
                'kategori_akun' => 'Kewajiban Jangka Pendek',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Hutang kepada supplier (kode lama)'
            ],
            [
                'kode_akun' => '211',
                'tipe_akun' => 'Liability',
                'nama_akun' => 'Hutang Gaji (BTKL)',
                'kategori_akun' => 'Kewajiban Jangka Pendek',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Hutang gaji tenaga kerja langsung (kode lama)'
            ],
            [
                'kode_akun' => '212',
                'tipe_akun' => 'Liability',
                'nama_akun' => 'Hutang BOP',
                'kategori_akun' => 'Kewajiban Jangka Pendek',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Hutang biaya overhead pabrik (kode lama)'
            ],
            
            // KEWAJIBAN (Kode 4 digit)
            [
                'kode_akun' => '2103',
                'tipe_akun' => 'Liability',
                'nama_akun' => 'Hutang Gaji',
                'kategori_akun' => 'Kewajiban Jangka Pendek',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Hutang gaji karyawan yang belum dibayar'
            ],
            [
                'kode_akun' => '2104',
                'tipe_akun' => 'Liability',
                'nama_akun' => 'Hutang BOP',
                'kategori_akun' => 'Kewajiban Jangka Pendek',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Hutang biaya overhead pabrik'
            ],

            // PENDAPATAN (Kode 3 digit untuk backward compatibility)
            [
                'kode_akun' => '401',
                'tipe_akun' => 'Revenue',
                'nama_akun' => 'Penjualan Produk',
                'kategori_akun' => 'Pendapatan Usaha',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'kredit',
                'keterangan' => 'Pendapatan dari penjualan produk (kode lama)'
            ],

            // BEBAN (Kode 3 digit untuk backward compatibility)
            [
                'kode_akun' => '501',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Harga Pokok Penjualan (HPP)',
                'kategori_akun' => 'Harga Pokok Penjualan',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Harga pokok penjualan produk (kode lama)'
            ],
            [
                'kode_akun' => '504',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Beban Penyusutan',
                'kategori_akun' => 'Beban Operasional',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Beban penyusutan aset tetap (kode lama)'
            ],
            [
                'kode_akun' => '505',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Beban Denda dan Bunga',
                'kategori_akun' => 'Beban Operasional',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Beban denda dan bunga (kode lama)'
            ],
            [
                'kode_akun' => '506',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Penyesuaian HPP (Diskon Pembelian)',
                'kategori_akun' => 'Beban Operasional',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Penyesuaian HPP dari diskon pembelian (kode lama)'
            ],
            
            // BEBAN (Kode 4 digit)
            [
                'kode_akun' => '5001',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Harga Pokok Penjualan',
                'kategori_akun' => 'Harga Pokok Penjualan',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Harga pokok penjualan produk'
            ],
            [
                'kode_akun' => '5103',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Beban Penyusutan',
                'kategori_akun' => 'Beban Operasional',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Beban penyusutan aset tetap'
            ],
            [
                'kode_akun' => '5104',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Beban Denda dan Bunga',
                'kategori_akun' => 'Beban Operasional',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Beban denda dan bunga'
            ],
            [
                'kode_akun' => '5105',
                'tipe_akun' => 'Expense',
                'nama_akun' => 'Penyesuaian HPP (Diskon Pembelian)',
                'kategori_akun' => 'Beban Operasional',
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => 'debit',
                'keterangan' => 'Penyesuaian HPP dari diskon pembelian (contra expense)'
            ],
        ];

        foreach ($additionalCoas as $coa) {
            Coa::updateOrCreate(['kode_akun' => $coa['kode_akun']], $coa);
        }
    }
}

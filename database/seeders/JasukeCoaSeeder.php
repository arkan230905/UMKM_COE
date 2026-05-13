<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JasukeCoaSeeder extends Seeder
{
    public function run(): void
    {
        // Data COA Lengkap untuk SIMACOST
        $coas = [
            // Nama, Kode, Tipe, Kategori, Saldo Normal
            ['Aset', '11', 'Asset', 'Aset Lancar', 'debit'],
            ['Kas Bank', '111', 'Asset', 'Aset Lancar', 'debit'],
            ['Kas', '112', 'Asset', 'Aset Lancar', 'debit'],
            ['Kas Kecil', '113', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Bahan Baku', '114', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Bahan Baku Jagung', '1141', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Bahan Pendukung', '115', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Bahan Pendukung Susu', '1151', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Bahan Pendukung Keju', '1152', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Bahan Pendukung Kemasan (Cup)', '1153', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Barang Jadi', '116', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Barang Jadi Jasuke', '1161', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Barang dalam Proses', '117', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Barang Dalam Proses - BBB', '1171', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Barang Dalam Proses - BTKL', '1172', 'Asset', 'Aset Lancar', 'debit'],
            ['Pers. Barang Dalam Proses - BOP', '1173', 'Asset', 'Aset Lancar', 'debit'],
            ['Piutang', '118', 'Asset', 'Aset Lancar', 'debit'],
            ['Peralatan', '119', 'Asset', 'Aset Tetap', 'debit'],
            ['Akumulasi Penyusutan Peralatan', '120', 'Asset', 'Aset Tetap', 'debit'],
            ['Mesin', '125', 'Asset', 'Aset Tetap', 'debit'],
            ['Akumulasi Penyusutan Mesin', '126', 'Asset', 'Aset Tetap', 'debit'],
            ['PPN Masukkan', '127', 'Asset', 'Aset Lancar', 'debit'],
            ['Hutang', '21', 'Kewajiban', 'Hutang Lancar', 'kredit'],
            ['Hutang Usaha', '210', 'Kewajiban', 'Hutang Lancar', 'kredit'],
            ['Hutang Gaji', '211', 'Kewajiban', 'Hutang Lancar', 'kredit'],
            ['PPN Keluaran', '212', 'Kewajiban', 'Hutang Lancar', 'kredit'],
            ['Modal', '31', 'Modal', 'Ekuitas', 'kredit'],
            ['Modal Usaha', '310', 'Modal', 'Ekuitas', 'kredit'],
            ['Prive', '311', 'Modal', 'Ekuitas', 'kredit'],
            ['Penjualan', '41', 'Pendapatan', 'Pendapatan Usaha', 'kredit'],
            ['Penjualan - Jasuke', '410', 'Pendapatan', 'Pendapatan Usaha', 'kredit'],
            ['Retur Penjualan', '42', 'Pendapatan', 'Pendapatan Usaha', 'kredit'],
            ['BBB - Biaya Bahan Baku', '51', 'Biaya', 'Beban Produksi', 'debit'],
            ['BBB - Jagung', '510', 'Biaya', 'Beban Produksi', 'debit'],
            ['Beban Tunjangan', '513', 'Biaya', 'Beban Operasional', 'debit'],
            ['Beban Asuransi', '514', 'Biaya', 'Beban Operasional', 'debit'],
            ['Beban Bonus', '515', 'Biaya', 'Beban Operasional', 'debit'],
            ['Potongan Gaji', '516', 'Biaya', 'Beban Operasional', 'debit'],
            ['BTKL', '52', 'Biaya', 'Beban Produksi', 'debit'],
            ['BTKL - Produksi Jasuke', '520', 'Biaya', 'Beban Produksi', 'debit'],
            ['BOP', '53', 'Biaya', 'Beban Produksi', 'debit'],
            ['BOP - Susu', '530', 'Biaya', 'Beban Produksi', 'debit'],
            ['BOP - Keju', '531', 'Biaya', 'Beban Produksi', 'debit'],
            ['BOP - Kemasan', '532', 'Biaya', 'Beban Produksi', 'debit'],
            ['Beban Sewa', '54', 'Biaya', 'Beban Operasional', 'debit'],
            ['BOP Lain', '55', 'Biaya', 'Beban Operasional', 'debit'],
            ['BOP - Listrik', '550', 'Biaya', 'Beban Operasional', 'debit'],
            ['BOP - Air', '551', 'Biaya', 'Beban Operasional', 'debit'],
            ['BOP - Gas', '552', 'Biaya', 'Beban Operasional', 'debit'],
            ['BOP - Penyusutan Peralatan', '553', 'Biaya', 'Beban Operasional', 'debit'],
            ['Harga Pokok Penjualan', '554', 'Biaya', 'Beban Produksi', 'debit'],
        ];

        foreach ($coas as $coa) {
            DB::table('accounts')->updateOrInsert(
                ['kode_akun' => $coa[1]],
                [
                    'company_id' => 1,
                    'nama_akun' => $coa[0],
                    'tipe_akun' => $coa[2],
                    'kategori_akun' => $coa[3],
                    'saldo_normal' => $coa[4],
                    'saldo_awal' => 0,
                    'is_akun_header' => (strlen($coa[1]) <= 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
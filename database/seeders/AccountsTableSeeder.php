<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;

class AccountsTableSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['kode_akun'=>'101', 'nama_akun'=>'Kas/Bank', 'tipe_akun'=>'asset', 'kategori_akun'=>''],
            ['kode_akun'=>'102', 'nama_akun'=>'Piutang Usaha', 'tipe_akun'=>'asset', 'kategori_akun'=>''],
            ['kode_akun'=>'121', 'nama_akun'=>'Persediaan Bahan Baku', 'tipe_akun'=>'asset', 'kategori_akun'=>''],
            ['kode_akun'=>'122', 'nama_akun'=>'Barang Dalam Proses (WIP)', 'tipe_akun'=>'asset', 'kategori_akun'=>''],
            ['kode_akun'=>'123', 'nama_akun'=>'Persediaan Barang Jadi', 'tipe_akun'=>'asset', 'kategori_akun'=>''],
            ['kode_akun'=>'124', 'nama_akun'=>'Akumulasi Penyusutan', 'tipe_akun'=>'asset', 'kategori_akun'=>''],
            ['kode_akun'=>'201', 'nama_akun'=>'Hutang Usaha', 'tipe_akun'=>'liability', 'kategori_akun'=>''],
            ['kode_akun'=>'211', 'nama_akun'=>'Akrual BTKL', 'tipe_akun'=>'liability', 'kategori_akun'=>''],
            ['kode_akun'=>'212', 'nama_akun'=>'Akrual BOP', 'tipe_akun'=>'liability', 'kategori_akun'=>''],
            ['kode_akun'=>'301', 'nama_akun'=>'Modal', 'tipe_akun'=>'equity', 'kategori_akun'=>''],
            ['kode_akun'=>'401', 'nama_akun'=>'Penjualan', 'tipe_akun'=>'revenue', 'kategori_akun'=>''],
            ['kode_akun'=>'501', 'nama_akun'=>'Harga Pokok Penjualan', 'tipe_akun'=>'expense', 'kategori_akun'=>''],
            ['kode_akun'=>'502', 'nama_akun'=>'BTKL', 'tipe_akun'=>'expense', 'kategori_akun'=>''],
            ['kode_akun'=>'503', 'nama_akun'=>'BOP', 'tipe_akun'=>'expense', 'kategori_akun'=>''],
            ['kode_akun'=>'504', 'nama_akun'=>'Beban Penyusutan', 'tipe_akun'=>'expense', 'kategori_akun'=>''],
        ];
        foreach ($accounts as $a) {
            Coa::firstOrCreate(['kode_akun'=>$a['kode_akun']], $a);
        }
    }
}

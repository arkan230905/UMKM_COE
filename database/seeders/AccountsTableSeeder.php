<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountsTableSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code'=>'101', 'name'=>'Kas/Bank', 'type'=>'asset'],
            ['code'=>'102', 'name'=>'Piutang Usaha', 'type'=>'asset'],
            ['code'=>'121', 'name'=>'Persediaan Bahan Baku', 'type'=>'asset'],
            ['code'=>'122', 'name'=>'Barang Dalam Proses (WIP)', 'type'=>'asset'],
            ['code'=>'123', 'name'=>'Persediaan Barang Jadi', 'type'=>'asset'],
            ['code'=>'124', 'name'=>'Akumulasi Penyusutan', 'type'=>'asset'],
            ['code'=>'201', 'name'=>'Hutang Usaha', 'type'=>'liability'],
            ['code'=>'211', 'name'=>'Akrual BTKL', 'type'=>'liability'],
            ['code'=>'212', 'name'=>'Akrual BOP', 'type'=>'liability'],
            ['code'=>'301', 'name'=>'Modal', 'type'=>'equity'],
            ['code'=>'401', 'name'=>'Penjualan', 'type'=>'revenue'],
            ['code'=>'501', 'name'=>'Harga Pokok Penjualan', 'type'=>'expense'],
            ['code'=>'502', 'name'=>'BTKL', 'type'=>'expense'],
            ['code'=>'503', 'name'=>'BOP', 'type'=>'expense'],
            ['code'=>'504', 'name'=>'Beban Penyusutan', 'type'=>'expense'],
        ];
        foreach ($accounts as $a) {
            Account::firstOrCreate(['code'=>$a['code']], $a);
        }
    }
}

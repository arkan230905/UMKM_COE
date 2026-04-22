<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // cek apakah sudah ada data
        if (DB::table('coa_templates')->count() > 0) {
            $this->command->info('COA template already exists. Skipping...');
            return;
        }

        $data = [

            // =========================
            // ASSET
            // =========================
            ['code' => '1110', 'name' => 'Kas', 'type' => 'ASSET'],
            ['code' => '1120', 'name' => 'Bank', 'type' => 'ASSET'],
            ['code' => '1130', 'name' => 'Piutang Usaha', 'type' => 'ASSET'],
            ['code' => '1140', 'name' => 'Persediaan Bahan Baku', 'type' => 'ASSET'],
            ['code' => '1150', 'name' => 'Persediaan Barang Dalam Proses', 'type' => 'ASSET'],
            ['code' => '1160', 'name' => 'Persediaan Barang Jadi', 'type' => 'ASSET'],
            ['code' => '1170', 'name' => 'Aset Tetap', 'type' => 'ASSET'],
            ['code' => '1180', 'name' => 'Akumulasi Penyusutan', 'type' => 'ASSET'],

            // =========================
            // LIABILITY
            // =========================
            ['code' => '2110', 'name' => 'Utang Usaha', 'type' => 'LIABILITY'],
            ['code' => '2120', 'name' => 'Utang Gaji', 'type' => 'LIABILITY'],
            ['code' => '2130', 'name' => 'Utang Pajak', 'type' => 'LIABILITY'],

            // =========================
            // EQUITY
            // =========================
            ['code' => '3110', 'name' => 'Modal Pemilik', 'type' => 'EQUITY'],
            ['code' => '3120', 'name' => 'Laba Ditahan', 'type' => 'EQUITY'],

            // =========================
            // REVENUE
            // =========================
            ['code' => '4110', 'name' => 'Penjualan Produk', 'type' => 'REVENUE'],

            // =========================
            // EXPENSE
            // =========================
            ['code' => '5110', 'name' => 'Biaya Bahan Baku', 'type' => 'EXPENSE'],
            ['code' => '5120', 'name' => 'Biaya Tenaga Kerja Langsung', 'type' => 'EXPENSE'],
            ['code' => '5130', 'name' => 'Biaya Overhead Pabrik', 'type' => 'EXPENSE'],
            ['code' => '5140', 'name' => 'Biaya Listrik Pabrik', 'type' => 'EXPENSE'],
            ['code' => '5150', 'name' => 'Biaya Penyusutan Mesin', 'type' => 'EXPENSE'],
            ['code' => '5160', 'name' => 'Biaya Administrasi', 'type' => 'EXPENSE'],
            ['code' => '5170', 'name' => 'Biaya Pemasaran', 'type' => 'EXPENSE'],
        ];

        DB::table('coa_templates')->insert($data);

        $this->command->info('COA template inserted successfully!');
    }
}
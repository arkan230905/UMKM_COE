<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PelunasanUtangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama jika ada
        DB::table('pelunasan_utangs')->truncate();
        
        // Insert data contoh
        DB::table('pelunasan_utangs')->insert([
            [
                'tanggal' => '2025-12-19',
                'vendor_id' => 1,
                'pembelian_id' => 1,
                'total_tagihan' => 1500000.00,
                'diskon' => 0.00,
                'denda_bunga' => 0.00,
                'dibayar_bersih' => 1500000.00,
                'metode_bayar' => 'tunai',
                'coa_kasbank' => '101',
                'keterangan' => 'Pelunasan utang pembelian bahan baku',
                'status' => 'lunas',
                'user_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tanggal' => '2025-12-18',
                'vendor_id' => 1,
                'pembelian_id' => 2,
                'total_tagihan' => 2000000.00,
                'diskon' => 100000.00,
                'denda_bunga' => 0.00,
                'dibayar_bersih' => 1900000.00,
                'metode_bayar' => 'transfer',
                'coa_kasbank' => '102',
                'keterangan' => 'Pelunasan dengan diskon 100rb',
                'status' => 'lunas',
                'user_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tanggal' => '2025-12-17',
                'vendor_id' => 1,
                'pembelian_id' => 3,
                'total_tagihan' => 800000.00,
                'diskon' => 0.00,
                'denda_bunga' => 50000.00,
                'dibayar_bersih' => 850000.00,
                'metode_bayar' => 'tunai',
                'coa_kasbank' => '101',
                'keterangan' => 'Pelunasan terlambat dengan denda',
                'status' => 'lunas',
                'user_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tanggal' => '2025-12-16',
                'vendor_id' => 1,
                'pembelian_id' => 4,
                'total_tagihan' => 1200000.00,
                'diskon' => 50000.00,
                'denda_bunga' => 0.00,
                'dibayar_bersih' => 1150000.00,
                'metode_bayar' => 'transfer',
                'coa_kasbank' => '102',
                'keterangan' => 'Pelunasan dengan diskon early payment',
                'status' => 'lunas',
                'user_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tanggal' => '2025-12-15',
                'vendor_id' => 1,
                'pembelian_id' => 5,
                'total_tagihan' => 900000.00,
                'diskon' => 0.00,
                'denda_bunga' => 0.00,
                'dibayar_bersih' => 900000.00,
                'metode_bayar' => 'tunai',
                'coa_kasbank' => '101',
                'keterangan' => 'Pelunasan tepat waktu',
                'status' => 'lunas',
                'user_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
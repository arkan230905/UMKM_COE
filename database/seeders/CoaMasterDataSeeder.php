<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class CoaMasterDataSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing COA data
        Coa::truncate();
        
        // COA data structure based on user requirements
        $coaData = [
            // ASSET
            ['kode_akun' => '11', 'nama_akun' => 'Aset', 'tipe_akun' => 'Aset', 'kategori_akun' => 'ASSET'],
            ['kode_akun' => '111', 'nama_akun' => 'AsetKas', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Kas Bank'],
            ['kode_akun' => '112', 'nama_akun' => 'AsetKas Kecil', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Kas Bank'],
            ['kode_akun' => '113', 'nama_akun' => 'AsetPers. Bahan Baku', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Baku'],
            ['kode_akun' => '114', 'nama_akun' => 'AsetPers. Bahan Baku ayam potong', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Baku'],
            ['kode_akun' => '1.141', 'nama_akun' => 'AsetPers. Bahan Baku ayam kampung', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Baku'],
            ['kode_akun' => '1.142', 'nama_akun' => 'AsetPers. Bahan Baku bebek', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Baku'],
            ['kode_akun' => '1.143', 'nama_akun' => 'AsetPers. Bahan Baku ayam lainnya', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Baku'],
            ['kode_akun' => '1.144', 'nama_akun' => 'AsetPers. Bahan Pendukung', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '115', 'nama_akun' => 'AsetPers. Bahan Pendukung Air', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.150', 'nama_akun' => 'AsetPers. Bahan Pendukung Minyak Goreng', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.151', 'nama_akun' => 'AsetPers. Bahan Pendukung Gas 30 Kg', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.152', 'nama_akun' => 'AsetPers. Bahan Pendukung Tepung Terigu', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.153', 'nama_akun' => 'AsetPers. Bahan Pendukung Tepung Maizena', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.154', 'nama_akun' => 'AsetPers. Bahan Pendukung Lada', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.155', 'nama_akun' => 'AsetPers. Bahan Pendukung Bubuk Kaldu', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.156', 'nama_akun' => 'AsetPers. Bahan Pendukung Listrik', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.157', 'nama_akun' => 'AsetPers. Bahan Pendukung Bubuk Bawang Putih', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.158', 'nama_akun' => 'AsetPers. Bahan Pendukung Kemasan', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.159', 'nama_akun' => 'AsetPers. Bahan Pendukung Cabe Merah', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Bahan Pendukung'],
            ['kode_akun' => '1.1510', 'nama_akun' => 'AsetPers. Barang Jadi Ayam Ketumbar', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Barang Jadi Ayam Ketumbar'],
            ['kode_akun' => '116', 'nama_akun' => 'AsetPers. Barang dalam Proses', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Pers. Barang dalam Proses'],
            ['kode_akun' => '117', 'nama_akun' => 'AsetPiutang', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Piutang'],
            ['kode_akun' => '118', 'nama_akun' => 'AsetPeralatan', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Peralatan'],
            ['kode_akun' => '119', 'nama_akun' => 'AsetAkumulasi Penyusutan Peralatan', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Akumulasi Penyusutan Peralatan'],
            ['kode_akun' => '120', 'nama_akun' => 'AsetGedung', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Gedung'],
            ['kode_akun' => '121', 'nama_akun' => 'AsetAkumulasi Penyusutan Gedung', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Akumulasi Penyusutan Gedung'],
            ['kode_akun' => '122', 'nama_akun' => 'AsetKendaraan', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Kendaraan'],
            ['kode_akun' => '123', 'nama_akun' => 'AsetAkumulasi Penyusutan Kendaraan', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Akumulasi Penyusutan Kendaraan'],
            ['kode_akun' => '124', 'nama_akun' => 'AsetMesin', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Mesin'],
            ['kode_akun' => '125', 'nama_akun' => 'AsetAkumulasi Penyusutan Mesin', 'tipe_akun' => 'Aset', 'kategori_akun' => 'Akumulasi Penyusutan Mesin'],
            ['kode_akun' => '126', 'nama_akun' => 'AsetPPN Masukkan', 'tipe_akun' => 'Aset', 'kategori_akun' => 'PPN Masukkan'],
            
            // HUTANG (LIABILITY)
            ['kode_akun' => '21', 'nama_akun' => 'Hutang', 'tipe_akun' => 'Kewajiban', 'kategori_akun' => 'Hutang'],
            ['kode_akun' => '210', 'nama_akun' => 'Hutang Usaha', 'tipe_akun' => 'Kewajiban', 'kategori_akun' => 'Hutang Usaha'],
            ['kode_akun' => '211', 'nama_akun' => 'Hutang Gaji', 'tipe_akun' => 'Kewajiban', 'kategori_akun' => 'Hutang Gaji'],
            ['kode_akun' => '212', 'nama_akun' => 'PPN Keluaran', 'tipe_akun' => 'Kewajiban', 'kategori_akun' => 'PPN Keluaran'],
            
            // MODAL (EQUITY)
            ['kode_akun' => '31', 'nama_akun' => 'Modal', 'tipe_akun' => 'Modal', 'kategori_akun' => 'Modal'],
            ['kode_akun' => '310', 'nama_akun' => 'Modal Usaha', 'tipe_akun' => 'Modal', 'kategori_akun' => 'Modal Usaha'],
            ['kode_akun' => '311', 'nama_akun' => 'Prive', 'tipe_akun' => 'Modal', 'kategori_akun' => 'Prive'],
            
            // PENJUALAN (REVENUE)
            ['kode_akun' => '41', 'nama_akun' => 'Penjualan', 'tipe_akun' => 'Pendapatan', 'kategori_akun' => 'Penjualan'],
            ['kode_akun' => '410', 'nama_akun' => 'Penjualan - Produk Ayam Ketumbar', 'tipe_akun' => 'Pendapatan', 'kategori_akun' => 'Penjualan - Produk Ayam Ketumbar'],
            
            // BIAYA BAHAN BAKU (EXPENSE)
            ['kode_akun' => '51', 'nama_akun' => 'BBB-Biaya Bahan Baku', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Bahan Baku'],
            ['kode_akun' => '510', 'nama_akun' => 'BBB-ayam potong', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Bahan Baku'],
            ['kode_akun' => '511', 'nama_akun' => 'BBB-ayam kampung', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Bahan Baku'],
            ['kode_akun' => '512', 'nama_akun' => 'BBB-bebek', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Bahan Baku'],
            
            // BIAYA TENAGA KERJA LANGSUNG
            ['kode_akun' => '52', 'nama_akun' => 'BTKL-Biaya Tenaga Kerja Langsung', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Langsung'],
            ['kode_akun' => '520', 'nama_akun' => 'BTKL-Chef 1', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Langsung'],
            ['kode_akun' => '521', 'nama_akun' => 'BTKL-Chef 2', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Langsung'],
            ['kode_akun' => '522', 'nama_akun' => 'BTKL-Chef 3', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Langsung'],
            
            // BIAYA OVERHEAD PABRIK
            ['kode_akun' => '53', 'nama_akun' => 'BOP-Biaya Overhead Pabrik', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '530', 'nama_akun' => 'BOP-Biaya Bahan Baku Tidak Langsung', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '531', 'nama_akun' => 'BOP-Air', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '532', 'nama_akun' => 'BOP-Minyak Goreng', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '533', 'nama_akun' => 'BOP- Gas 30 Kg', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '534', 'nama_akun' => 'BOP-Tepung Terigu', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '535', 'nama_akun' => 'BOP-Tepung Maizena', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '536', 'nama_akun' => 'BOP- Lada', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '537', 'nama_akun' => 'BOP- Bubuk Kaldu', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '538', 'nama_akun' => 'BOP- Listrik', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '539', 'nama_akun' => 'BOP- Bubuk Bawang Putih', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '540', 'nama_akun' => 'BOP-Kemasan', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            ['kode_akun' => '541', 'nama_akun' => 'BOP-Cabe Merah', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Overhead Pabrik'],
            
            // BIAYA TENAGA KERJA TIDAK LANGSUNG - Fixed duplicate codes
            ['kode_akun' => '54', 'nama_akun' => 'BOP BTKTL-Biaya Tenaga Kerja Tidak Langsung', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung'],
            ['kode_akun' => '542', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Pemasaran', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung'],
            ['kode_akun' => '543', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Kemasan', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung'],
            ['kode_akun' => '544', 'nama_akun' => 'BOP BTKTL - Biaya Satpam Pabrik', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung'],
            ['kode_akun' => '545', 'nama_akun' => 'BOP BTKTL - Biaya Cleaning Service', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung'],
            ['kode_akun' => '546', 'nama_akun' => 'BOP BTKTL - Biaya Mandor', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung'],
            ['kode_akun' => '547', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Keuangan', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung'],
            ['kode_akun' => '548', 'nama_akun' => 'BOP BTKTL - BTKTL Lainnya', 'tipe_akun' => 'Beban', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung'],
            
            // BOP TIDAK LANGSUNG LAINNYA - Fixed duplicate codes
            ['kode_akun' => '55', 'nama_akun' => 'BOP TL - BOP Tidak Langsung Lainnya', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '550', 'nama_akun' => 'BOP TL - Biaya Listrik', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '551', 'nama_akun' => 'BOP TL - Sewa Tempat', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '552', 'nama_akun' => 'BOP TL - Biaya Penyusutan Gedung', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '553', 'nama_akun' => 'BOP TL - Biaya Penyusutan Peralatan', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '554', 'nama_akun' => 'BOP TL - Biaya Penyusutan Kendaraan', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '555', 'nama_akun' => 'BOP TL - Biaya Penyusutan Mesin', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '556', 'nama_akun' => 'BOP TL - Biaya Air', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '557', 'nama_akun' => 'BOP TL - Lainnya', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '558', 'nama_akun' => 'Beban Transport Pembelian', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
            ['kode_akun' => '559', 'nama_akun' => 'Diskon Pembelian', 'tipe_akun' => 'Beban', 'kategori_akun' => 'BOP Tidak Langsung Lainnya'],
        ];

        // Insert COA data
        foreach ($coaData as $index => $data) {
            // Determine saldo normal based on account type
            $saldoNormal = 'debit';
            if (in_array($data['tipe_akun'], ['Kewajiban', 'Modal', 'Pendapatan'])) {
                $saldoNormal = 'kredit';
            }

            Coa::create([
                'kode_akun' => $data['kode_akun'],
                'nama_akun' => $data['nama_akun'],
                'tipe_akun' => $data['tipe_akun'],
                'kategori_akun' => $data['kategori_akun'],
                'saldo_normal' => $saldoNormal,
                'saldo_awal' => 0,
                'keterangan' => null,
                'posted_saldo_awal' => 0,
            ]);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('COA Master Data has been seeded successfully!');
    }
}

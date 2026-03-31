<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Coa;

class CoaSeederFinal extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coaData = [
            // ASSET
            ['kode_akun' => '11', 'nama_akun' => 'ASSET', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '111', 'nama_akun' => 'Kas Bank', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Kas & Bank', 'saldo_normal' => 'debit'],
            ['kode_akun' => '112', 'nama_akun' => 'Kas', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Kas & Bank', 'saldo_normal' => 'debit'],
            ['kode_akun' => '113', 'nama_akun' => 'Kas Kecil', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Kas & Bank', 'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Baku
            ['kode_akun' => '114', 'nama_akun' => 'Pers. Bahan Baku', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1141', 'nama_akun' => 'Pers. Bahan Baku ayam potong', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1142', 'nama_akun' => 'Pers. Bahan Baku ayam kampung', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1143', 'nama_akun' => 'Pers. Bahan Baku bebek', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1144', 'nama_akun' => 'Pers. Bahan Baku ayam lainnya', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Pendukung
            ['kode_akun' => '115', 'nama_akun' => 'Pers. Bahan Pendukung', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1150', 'nama_akun' => 'Pers. Bahan Pendukung Air', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1151', 'nama_akun' => 'Pers. Bahan Pendukung Minyak Goreng', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1152', 'nama_akun' => 'Pers. Bahan Pendukung Gas 30 Kg', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1153', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Terigu', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1154', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Maizena', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1155', 'nama_akun' => 'Pers. Bahan Pendukung Lada', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1156', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Kaldu', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1157', 'nama_akun' => 'Pers. Bahan Pendukung Listrik', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1158', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Bawang Putih', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1159', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '11510', 'nama_akun' => 'Pers. Bahan Pendukung Cabe Merah', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            
            // Persediaan Lainnya
            ['kode_akun' => '116', 'nama_akun' => 'Pers. Barang Jadi Ayam Ketumbar', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '117', 'nama_akun' => 'Pers. Barang dalam Proses', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '118', 'nama_akun' => 'Piutang', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Piutang', 'saldo_normal' => 'debit'],
            
            // Aset Tetap
            ['kode_akun' => '119', 'nama_akun' => 'Peralatan', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap', 'saldo_normal' => 'debit'],
            ['kode_akun' => '120', 'nama_akun' => 'Akumulasi Penyusutan Peralatan', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '121', 'nama_akun' => 'Gedung', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap', 'saldo_normal' => 'debit'],
            ['kode_akun' => '122', 'nama_akun' => 'Akumulasi Penyusutan Gedung', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '123', 'nama_akun' => 'Kendaraan', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap', 'saldo_normal' => 'debit'],
            ['kode_akun' => '124', 'nama_akun' => 'Akumulasi Penyusutan Kendaraan', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '125', 'nama_akun' => 'Mesin', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap', 'saldo_normal' => 'debit'],
            ['kode_akun' => '126', 'nama_akun' => 'Akumulasi Penyusutan Mesin', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Tetap', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '127', 'nama_akun' => 'PPN Masukkan', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset Lancar', 'saldo_normal' => 'debit'],
            
            // KEWAJIBAN
            ['kode_akun' => '21', 'nama_akun' => 'Hutang', 'tipe_akun' => 'Liability', 'kategori_akun' => 'Kewajiban', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '210', 'nama_akun' => 'Hutang Usaha', 'tipe_akun' => 'Liability', 'kategori_akun' => 'Hutang', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '211', 'nama_akun' => 'Hutang Gaji', 'tipe_akun' => 'Liability', 'kategori_akun' => 'Hutang', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '212', 'nama_akun' => 'PPN Keluaran', 'tipe_akun' => 'Liability', 'kategori_akun' => 'Hutang', 'saldo_normal' => 'kredit'],
            
            // MODAL
            ['kode_akun' => '31', 'nama_akun' => 'Modal', 'tipe_akun' => 'Equity', 'kategori_akun' => 'Modal', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '310', 'nama_akun' => 'Modal Usaha', 'tipe_akun' => 'Equity', 'kategori_akun' => 'Modal', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '311', 'nama_akun' => 'Prive', 'tipe_akun' => 'Equity', 'kategori_akun' => 'Modal', 'saldo_normal' => 'debit'],
            
            // PENDAPATAN
            ['kode_akun' => '41', 'nama_akun' => 'Penjualan', 'tipe_akun' => 'Revenue', 'kategori_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '410', 'nama_akun' => 'Penjualan - Produk Ayam Ketumbar', 'tipe_akun' => 'Revenue', 'kategori_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            
            // BIAYA BAHAN BAKU (BBB)
            ['kode_akun' => '51', 'nama_akun' => 'BBB-Biaya Bahan Baku', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Bahan Baku', 'saldo_normal' => 'debit'],
            ['kode_akun' => '510', 'nama_akun' => 'BBB-ayam potong', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Bahan Baku', 'saldo_normal' => 'debit'],
            ['kode_akun' => '511', 'nama_akun' => 'BBB-ayam kampung', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Bahan Baku', 'saldo_normal' => 'debit'],
            ['kode_akun' => '512', 'nama_akun' => 'BBB-bebek', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Bahan Baku', 'saldo_normal' => 'debit'],
            
            // BIAYA TENAGA KERJA LANGSUNG (BTKL)
            ['kode_akun' => '52', 'nama_akun' => 'BTKL-Biaya Tenaga Kerja Langsung', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '520', 'nama_akun' => 'BTKL-Chef 1', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '521', 'nama_akun' => 'BTKL-Chef 2', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '522', 'nama_akun' => 'BTKL-Chef 3', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Langsung', 'saldo_normal' => 'debit'],
            
            // BIAYA OVERHEAD PABRIK (BOP)
            ['kode_akun' => '53', 'nama_akun' => 'BOP-Biaya Overhead Pabrik', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '530', 'nama_akun' => 'BOP-Biaya Bahan Baku Tidak Langsung', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '531', 'nama_akun' => 'BOP-Air', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '532', 'nama_akun' => 'BOP-Minyak Goreng', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '533', 'nama_akun' => 'BOP-Gas 30 Kg', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '534', 'nama_akun' => 'BOP-Ketumbar Bubuk', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '535', 'nama_akun' => 'BOP-Bawang Putih', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '536', 'nama_akun' => 'BOP-Tepung Maizena', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '537', 'nama_akun' => 'BOP-Merica Bubuk', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '538', 'nama_akun' => 'BOP-Listrik', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '539', 'nama_akun' => 'BOP-Bawang Merah', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '540', 'nama_akun' => 'BOP-Kemasan', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '541', 'nama_akun' => 'BOP-Cabe Merah', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '542', 'nama_akun' => 'BOP-Lada Hitam', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            
            // BIAYA TENAGA KERJA TIDAK LANGSUNG (BTKTL) - menggunakan kode yang sama dengan BOP
            ['kode_akun' => '54', 'nama_akun' => 'BOP BTKTL-Biaya Tenaga Kerja Tidak Langsung', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '543', 'nama_akun' => 'BOP BTKTL - Biaya Cleaning Service', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '544', 'nama_akun' => 'BOP BTKTL - Biaya Mandor', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '545', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Keuangan', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '546', 'nama_akun' => 'BOP BTKTL - BTKTL Lainnya', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            
            // BOP TIDAK LANGSUNG LAINNYA
            ['kode_akun' => '55', 'nama_akun' => 'BOP TL - BOP Tidak Langsung Lainnya', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '550', 'nama_akun' => 'BOP TL - Biaya Listrik', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '551', 'nama_akun' => 'BOP TL - Sewa Tempat', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '552', 'nama_akun' => 'BOP TL - Biaya Penyusutan Gedung', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '553', 'nama_akun' => 'BOP TL - Biaya Air', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '554', 'nama_akun' => 'BOP TL - Lainnya', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '555', 'nama_akun' => 'Beban Transport Pembelian', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '556', 'nama_akun' => 'Diskon Pembelian', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
        ];

        foreach ($coaData as $coa) {
            Coa::updateOrCreate(
                ['kode_akun' => $coa['kode_akun']],
                [
                    'nama_akun' => $coa['nama_akun'],
                    'tipe_akun' => $coa['tipe_akun'],
                    'kategori_akun' => $coa['kategori_akun'],
                    'saldo_normal' => $coa['saldo_normal'],
                    'saldo_awal' => 0,
                ]
            );
        }

        // Handle dual purpose accounts (540-542 for both BOP and BTKTL)
        $dualPurposeUpdates = [
            '540' => 'BOP-Kemasan / BOP BTKTL - Biaya Pegawai Pemasaran',
            '541' => 'BOP-Cabe Merah / BOP BTKTL - Biaya Pegawai Kemasan', 
            '542' => 'BOP-Lada Hitam / BOP BTKTL - Biaya Satpam Pabrik',
        ];

        foreach ($dualPurposeUpdates as $kode => $nama) {
            Coa::where('kode_akun', $kode)->update([
                'nama_akun' => $nama,
                'kategori_akun' => 'Biaya Overhead Pabrik'
            ]);
        }

        $this->command->info('COA seeder final completed successfully!');
        $this->command->info('Note: Accounts 540-542 serve dual purpose for both BOP and BTKTL as requested.');
        $this->command->info('Accounts 543-546 are dedicated BTKTL accounts.');
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;

class DefaultCoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat COA default untuk setiap perusahaan baru dengan multi-tenant isolation
     */
    public function run(int $userId): void
    {
        $defaultCoas = [
            // ASSET
            ['kode_akun' => '11', 'nama_akun' => 'ASSET', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            
            // Kas Bank
            ['kode_akun' => '111', 'nama_akun' => 'Kas Bank', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '112', 'nama_akun' => 'Kas', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '113', 'nama_akun' => 'Kas Kecil', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Baku
            ['kode_akun' => '114', 'nama_akun' => 'Pers. Bahan Baku', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1141', 'nama_akun' => 'Pers. Bahan Baku ayam potong', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1142', 'nama_akun' => 'Pers. Bahan Baku ayam kampung', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1143', 'nama_akun' => 'Pers. Bahan Baku bebek', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1144', 'nama_akun' => 'Pers. Bahan Baku ayam lainnya', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Pendukung
            ['kode_akun' => '115', 'nama_akun' => 'Pers. Bahan Pendukung', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1150', 'nama_akun' => 'Pers. Bahan Pendukung Air', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1151', 'nama_akun' => 'Pers. Bahan Pendukung Minyak Goreng', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1152', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Terigu', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1153', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Maizena', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1154', 'nama_akun' => 'Pers. Bahan Pendukung Lada', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1155', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Kaldu', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1156', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Bawang Putih', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1157', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            
            // Persediaan Barang Jadi
            ['kode_akun' => '116', 'nama_akun' => 'Pers. Barang Jadi', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1161', 'nama_akun' => 'Pers. Barang Jadi Ayam Crispy Macdi', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1162', 'nama_akun' => 'Pers. Barang Jadi Ayam Goreng Bundo', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            
            // Barang dalam Proses
            ['kode_akun' => '117', 'nama_akun' => 'Pers. Barang dalam Proses', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            
            // Piutang
            ['kode_akun' => '118', 'nama_akun' => 'Piutang', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            
            // Peralatan
            ['kode_akun' => '119', 'nama_akun' => 'Peralatan', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '120', 'nama_akun' => 'Akumulasi Penyusutan Peralatan', 'tipe_akun' => 'Asset', 'saldo_normal' => 'kredit'],
            
            // Gedung
            ['kode_akun' => '121', 'nama_akun' => 'Gedung', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '122', 'nama_akun' => 'Akumulasi Penyusutan Gedung', 'tipe_akun' => 'Asset', 'saldo_normal' => 'kredit'],
            
            // Kendaraan
            ['kode_akun' => '123', 'nama_akun' => 'Kendaraan', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '124', 'nama_akun' => 'Akumulasi Penyusutan Kendaraan', 'tipe_akun' => 'Asset', 'saldo_normal' => 'kredit'],
            
            // Mesin
            ['kode_akun' => '125', 'nama_akun' => 'Mesin', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '126', 'nama_akun' => 'Akumulasi Penyusutan Mesin', 'tipe_akun' => 'Asset', 'saldo_normal' => 'kredit'],
            
            // PPN Masukkan
            ['kode_akun' => '127', 'nama_akun' => 'PPN Masukkan', 'tipe_akun' => 'Asset', 'saldo_normal' => 'debit'],
            
            // KEWAJIBAN
            ['kode_akun' => '21', 'nama_akun' => 'Hutang', 'tipe_akun' => 'Liability', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '210', 'nama_akun' => 'Hutang Usaha', 'tipe_akun' => 'Liability', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '211', 'nama_akun' => 'Hutang Gaji', 'tipe_akun' => 'Liability', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '212', 'nama_akun' => 'PPN Keluaran', 'tipe_akun' => 'Liability', 'saldo_normal' => 'kredit'],
            
            // MODAL
            ['kode_akun' => '31', 'nama_akun' => 'Modal', 'tipe_akun' => 'Equity', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '310', 'nama_akun' => 'Modal Usaha', 'tipe_akun' => 'Equity', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '311', 'nama_akun' => 'Prive', 'tipe_akun' => 'Equity', 'saldo_normal' => 'debit'],
            
            // PENDAPATAN
            ['kode_akun' => '41', 'nama_akun' => 'Penjualan', 'tipe_akun' => 'Revenue', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '410', 'nama_akun' => 'Penjualan - Produk Ayam Crispy Macdi', 'tipe_akun' => 'Revenue', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '411', 'nama_akun' => 'Penjualan - Produk Ayam Goreng Bundo', 'tipe_akun' => 'Revenue', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '42', 'nama_akun' => 'Retur Penjualan', 'tipe_akun' => 'Revenue', 'saldo_normal' => 'debit'],
            ['kode_akun' => '43', 'nama_akun' => 'Pendapatan Ongkir', 'tipe_akun' => 'Revenue', 'saldo_normal' => 'kredit'],
            
            // BIAYA BAHAN BAKU
            ['kode_akun' => '51', 'nama_akun' => 'BBB-Biaya Bahan Baku', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '510', 'nama_akun' => 'BBB-ayam potong', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '511', 'nama_akun' => 'BBB-ayam kampung', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '512', 'nama_akun' => 'BBB-bebek', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            
            // BIAYA TENAGA KERJA LANGSUNG
            ['kode_akun' => '52', 'nama_akun' => 'BTKL-Biaya Tenaga Kerja Langsung', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '520', 'nama_akun' => 'BTKL-Perbumbuan', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '521', 'nama_akun' => 'BTKL-Penggorengan', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '522', 'nama_akun' => 'BTKL-Pengemasan', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            
            // BIAYA OVERHEAD PABRIK
            ['kode_akun' => '53', 'nama_akun' => 'BOP-Biaya Overhead Pabrik', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '530', 'nama_akun' => 'BOP-Biaya Bahan Baku Tidak Langsung', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '531', 'nama_akun' => 'BOP-Air', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '532', 'nama_akun' => 'BOP-Minyak Goreng', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '533', 'nama_akun' => 'BOP-Tepung Terigu', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '534', 'nama_akun' => 'BOP-Tepung Maizena', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '535', 'nama_akun' => 'BOP- Lada', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '536', 'nama_akun' => 'BOP- Bubuk Kaldu', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '537', 'nama_akun' => 'BOP- Bubuk Bawang Putih', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '538', 'nama_akun' => 'BOP-Kemasan', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            
            // BIAYA TENAGA KERJA TIDAK LANGSUNG
            ['kode_akun' => '54', 'nama_akun' => 'BOP BTKTL-Biaya Tenaga Kerja Tidak Langsung', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '540', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Pemasaran', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '541', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Kemasan', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '542', 'nama_akun' => 'BOP BTKTL - Biaya Satpam Pabrik', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '543', 'nama_akun' => 'BOP BTKTL - Biaya Cleaning Service', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '544', 'nama_akun' => 'BOP BTKTL - Biaya Mandor', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '545', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Keuangan', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '546', 'nama_akun' => 'BOP BTKTL - BTKTL Lainnya', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            
            // BOP TIDAK LANGSUNG LAINNYA
            ['kode_akun' => '55', 'nama_akun' => 'BOP TL - BOP Tidak Langsung Lainnya', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '550', 'nama_akun' => 'BOP TL - Biaya Listrik', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '551', 'nama_akun' => 'BOP TL - Sewa Tempat', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '552', 'nama_akun' => 'BOP TL - Biaya Penyusutan Gedung', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '553', 'nama_akun' => 'BOP TL - Biaya Penyusutan Peralatan', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '554', 'nama_akun' => 'BOP TL - Biaya Penyusutan Kendaraan', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '555', 'nama_akun' => 'BOP TL - Biaya Penyusutan Mesin', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '556', 'nama_akun' => 'BOP TL - Biaya Air', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '557', 'nama_akun' => 'BOP TL - Lainnya', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '558', 'nama_akun' => 'Beban Transport Pembelian', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
            ['kode_akun' => '559', 'nama_akun' => 'Diskon Pembelian', 'tipe_akun' => 'Expense', 'saldo_normal' => 'debit'],
        ];

        foreach ($defaultCoas as $coaData) {
            Coa::firstOrCreate(
                [
                    'user_id' => $userId,
                    'kode_akun' => $coaData['kode_akun']
                ],
                [
                    'nama_akun' => $coaData['nama_akun'],
                    'tipe_akun' => $coaData['tipe_akun'],
                    'kategori_akun' => $coaData['kategori_akun'] ?? '',
                    'is_akun_header' => $coaData['is_akun_header'] ?? false,
                    'saldo_normal' => $coaData['saldo_normal'] ?? 'debit',
                    'saldo_awal' => 0,
                    'posted_saldo_awal' => false,
                ]
            );
        }
    }
}

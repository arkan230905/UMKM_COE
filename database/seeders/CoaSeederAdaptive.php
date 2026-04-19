<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Coa;
use Illuminate\Support\Facades\Schema;

class CoaSeederAdaptive extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek kolom yang tersedia di tabel coas
        $columns = Schema::getColumnListing('coas');
        
        $this->command->info('Kolom yang tersedia di tabel coas: ' . implode(', ', $columns));
        
        // Data COA lengkap sesuai dengan struktur yang diminta
        $coaData = [
            // ASSET
            ['kode_akun' => '11', 'nama_akun' => 'ASSET', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Aset', 'saldo_normal' => 'debit'],
            ['kode_akun' => '111', 'nama_akun' => 'Kas Bank', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Kas & Bank', 'saldo_normal' => 'debit'],
            ['kode_akun' => '112', 'nama_akun' => 'Kas', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Kas & Bank', 'saldo_normal' => 'debit'],
            ['kode_akun' => '113', 'nama_akun' => 'Kas Kecil', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Kas & Bank', 'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Baku
            ['kode_akun' => '114', 'nama_akun' => 'Pers. Bahan Baku', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.141', 'nama_akun' => 'Pers. Bahan Baku ayam potong', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.142', 'nama_akun' => 'Pers. Bahan Baku ayam kampung', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.143', 'nama_akun' => 'Pers. Bahan Baku bebek', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.144', 'nama_akun' => 'Pers. Bahan Baku ayam lainnya', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Pendukung
            ['kode_akun' => '115', 'nama_akun' => 'Pers. Bahan Pendukung', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.150', 'nama_akun' => 'Pers. Bahan Pendukung Air', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.151', 'nama_akun' => 'Pers. Bahan Pendukung Minyak Goreng', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.152', 'nama_akun' => 'Pers. Bahan Pendukung Gas 30 Kg', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.153', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Terigu', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.154', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Maizena', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.155', 'nama_akun' => 'Pers. Bahan Pendukung Lada', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.156', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Kaldu', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.157', 'nama_akun' => 'Pers. Bahan Pendukung Listrik', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.158', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Bawang Putih', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.159', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            ['kode_akun' => '1.1510', 'nama_akun' => 'Pers. Bahan Pendukung Cabe Merah', 'tipe_akun' => 'Asset', 'kategori_akun' => 'Persediaan', 'saldo_normal' => 'debit'],
            
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
            ['kode_akun' => '533', 'nama_akun' => 'BOP- Gas 30 Kg', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '534', 'nama_akun' => 'BOP-Tepung Terigu', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '535', 'nama_akun' => 'BOP-Tepung Maizena', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '536', 'nama_akun' => 'BOP- Lada', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '537', 'nama_akun' => 'BOP- Bubuk Kaldu', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '538', 'nama_akun' => 'BOP- Listrik', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            ['kode_akun' => '539', 'nama_akun' => 'BOP- Bubuk Bawang Putih', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Overhead Pabrik', 'saldo_normal' => 'debit'],
            
            // BIAYA TENAGA KERJA TIDAK LANGSUNG (BTKTL)
            ['kode_akun' => '54', 'nama_akun' => 'BOP BTKTL-Biaya Tenaga Kerja Tidak Langsung', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '540', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Pemasaran', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '541', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Kemasan', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '542', 'nama_akun' => 'BOP BTKTL - Biaya Satpam Pabrik', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '543', 'nama_akun' => 'BOP BTKTL - Biaya Cleaning Service', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '544', 'nama_akun' => 'BOP BTKTL - Biaya Mandor', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '545', 'nama_akun' => 'BOP BTKTL - Biaya Pegawai Keuangan', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            ['kode_akun' => '546', 'nama_akun' => 'BOP BTKTL - BTKTL Lainnya', 'tipe_akun' => 'Expense', 'kategori_akun' => 'Biaya Tenaga Kerja Tidak Langsung', 'saldo_normal' => 'debit'],
            
            // BOP TIDAK LANGSUNG LAINNYA
            ['kode_akun' => '55', 'nama_akun' => 'BOP TL - BOP Tidak Langsung Lainnya', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '550', 'nama_akun' => 'BOP TL - Biaya Listrik', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '551', 'nama_akun' => 'BOP TL - Sewa Tempat', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '552', 'nama_akun' => 'BOP TL - Biaya Penyusutan Gedung', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '553', 'nama_akun' => 'BOP TL - Biaya Penyusutan Peralatan', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '554', 'nama_akun' => 'BOP TL - Biaya Penyusutan Kendaraan', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '555', 'nama_akun' => 'BOP TL - Biaya Penyusutan Mesin', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '556', 'nama_akun' => 'BOP TL - Biaya Air', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '557', 'nama_akun' => 'BOP TL - Lainnya', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '558', 'nama_akun' => 'Beban Transport Pembelian', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
            ['kode_akun' => '559', 'nama_akun' => 'Diskon Pembelian', 'tipe_akun' => 'Expense', 'kategori_akun' => 'BOP Tidak Langsung Lainnya', 'saldo_normal' => 'debit'],
        ];
        foreach ($coaData as $coa) {
            // Buat array data yang akan disimpan berdasarkan kolom yang tersedia
            $dataToSave = [
                'kode_akun' => $coa['kode_akun'],
                'nama_akun' => $coa['nama_akun'],
                'tipe_akun' => $coa['tipe_akun'],
                'kategori_akun' => $coa['kategori_akun'],
                'saldo_normal' => $coa['saldo_normal'],
                'saldo_awal' => 0,
            ];

            // Tambahkan kolom tambahan jika tersedia
            if (in_array('kode_induk', $columns)) {
                $dataToSave['kode_induk'] = $this->getKodeInduk($coa['kode_akun']);
            }
            
            if (in_array('keterangan', $columns)) {
                $dataToSave['keterangan'] = null;
            }
            
            if (in_array('is_akun_header', $columns)) {
                $dataToSave['is_akun_header'] = $this->isAkunHeader($coa['kode_akun']);
            }
            
            if (in_array('tanggal_saldo_awal', $columns)) {
                $dataToSave['tanggal_saldo_awal'] = '2026-03-01 00:00:00';
            }
            
            Coa::firstOrCreate(
                ['kode_akun' => $coa['kode_akun']],
                $dataToSave
            );
        }
        
        $this->command->info('COA seeder completed successfully!');
        $this->command->info('Total accounts processed: ' . count($coaData));
    }

    private function getKodeInduk($kodeAkun)
    {
        // Tentukan kode induk berdasarkan hierarki
        if (in_array($kodeAkun, ['11', '21', '31', '41', '51', '52', '53', '54', '55'])) {
            return null; // Akun header utama
        }
        
        if (in_array($kodeAkun, ['114', '115'])) {
            return '11'; // Sub-header di bawah ASSET
        }
        
        // Detail bahan baku (1.141, 1.142, etc.)
        if (str_starts_with($kodeAkun, '1.14')) {
            return '114';
        }
        
        // Detail bahan pendukung (1.150, 1.151, etc.)
        if (str_starts_with($kodeAkun, '1.15')) {
            return '115';
        }
        
        // Default: ambil digit pertama sebagai induk untuk kode 3 digit
        if (strlen($kodeAkun) == 3) {
            return substr($kodeAkun, 0, 2);
        }
        
        // Untuk kode 2 digit, induk adalah digit pertama
        if (strlen($kodeAkun) == 2) {
            return substr($kodeAkun, 0, 1);
        }
        
        return null;
    }

    private function isAkunHeader($kodeAkun)
    {
        // Tentukan apakah akun adalah header
        $headerAccounts = ['11', '21', '31', '41', '51', '52', '53', '54', '55', '114', '115'];
        return in_array($kodeAkun, $headerAccounts) ? 1 : 0;
    }
}
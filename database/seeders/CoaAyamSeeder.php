<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CoaAyamSeeder extends Seeder
{
    /**
     * Run the database seeder.
     * 
     * Seeder ini khusus untuk bisnis AYAM (bukan jagung)
     * Struktur COA lengkap dengan:
     * - Aset (Persediaan Bahan Baku Ayam, Bahan Pendukung)
     * - Kewajiban
     * - Modal
     * - Pendapatan (Penjualan Produk Ayam)
     * - Biaya Produksi (BBB, BTKL, BOP)
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $coas = [
            // ==========================================
            // ASET (1xxx)
            // ==========================================
            ['kode_akun' => '11',   'nama_akun' => 'Aset',                                            'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '111',  'nama_akun' => 'Kas Bank',                                        'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1111', 'nama_akun' => 'Bank BCA',                                        'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1112', 'nama_akun' => 'Bank Mandiri',                                    'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '112',  'nama_akun' => 'Kas',                                             'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '113',  'nama_akun' => 'Kas Kecil',                                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Baku - AYAM
            ['kode_akun' => '114',  'nama_akun' => 'Pers. Bahan Baku',                                'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1141', 'nama_akun' => 'Pers. Bahan Baku Ayam Potong',                    'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1142', 'nama_akun' => 'Pers. Bahan Baku Ayam Kampung',                   'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1143', 'nama_akun' => 'Pers. Bahan Baku Bebek',                          'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1144', 'nama_akun' => 'Pers. Bahan Baku Ayam Lainnya',                   'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Pendukung
            ['kode_akun' => '115',  'nama_akun' => 'Pers. Bahan Pendukung',                           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1150', 'nama_akun' => 'Pers. Bahan Pendukung Air',                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1151', 'nama_akun' => 'Pers. Bahan Pendukung Minyak Goreng',             'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1152', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Terigu',             'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1153', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Maizena',            'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1154', 'nama_akun' => 'Pers. Bahan Pendukung Lada',                      'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1155', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Kaldu',               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1156', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Bawang Putih',        'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1157', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan',                   'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Persediaan Barang Jadi
            ['kode_akun' => '116',  'nama_akun' => 'Pers. Barang Jadi',                               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1161', 'nama_akun' => 'Pers. Barang Jadi Ayam Crispy Macdi',             'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1162', 'nama_akun' => 'Pers. Barang Jadi Ayam Goreng Bundo',             'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Persediaan Barang Dalam Proses (WIP)
            ['kode_akun' => '117',  'nama_akun' => 'Pers. Barang Dalam Proses',                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1174', 'nama_akun' => 'Pers. Barang Dalam Proses - BBB (WIP BBB)',       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1175', 'nama_akun' => 'Pers. Barang Dalam Proses - BTKL (WIP BTKL)',     'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1176', 'nama_akun' => 'Pers. Barang Dalam Proses - BOP (WIP BOP)',       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Aset Lainnya
            ['kode_akun' => '118',  'nama_akun' => 'Piutang',                                         'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '119',  'nama_akun' => 'Peralatan',                                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '120',  'nama_akun' => 'Akumulasi Penyusutan Peralatan',                  'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '121',  'nama_akun' => 'Gedung',                                          'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '122',  'nama_akun' => 'Akumulasi Penyusutan Gedung',                     'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '123',  'nama_akun' => 'Kendaraan',                                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '124',  'nama_akun' => 'Akumulasi Penyusutan Kendaraan',                  'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '125',  'nama_akun' => 'Mesin',                                           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '126',  'nama_akun' => 'Akumulasi Penyusutan Mesin',                      'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '127',  'nama_akun' => 'PPN Masukkan',                                    'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // ==========================================
            // KEWAJIBAN (2xxx)
            // ==========================================
            ['kode_akun' => '21',   'nama_akun' => 'Hutang',                                          'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            ['kode_akun' => '210',  'nama_akun' => 'Hutang Usaha',                                    'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            ['kode_akun' => '211',  'nama_akun' => 'Hutang Gaji',                                     'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            ['kode_akun' => '212',  'nama_akun' => 'PPN Keluaran',                                    'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            
            // ==========================================
            // MODAL (3xxx)
            // ==========================================
            ['kode_akun' => '31',   'nama_akun' => 'Modal',                                           'tipe_akun' => 'Modal',      'saldo_normal' => 'kredit'],
            ['kode_akun' => '310',  'nama_akun' => 'Modal Usaha',                                     'tipe_akun' => 'Modal',      'saldo_normal' => 'kredit'],
            ['kode_akun' => '311',  'nama_akun' => 'Prive',                                           'tipe_akun' => 'Modal',      'saldo_normal' => 'kredit'],
            
            // ==========================================
            // PENDAPATAN (4xxx)
            // ==========================================
            ['kode_akun' => '41',   'nama_akun' => 'Penjualan',                                       'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '410',  'nama_akun' => 'Penjualan - Produk Ayam Crispy Macdi',            'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '411',  'nama_akun' => 'Penjualan - Produk Ayam Goreng Bundo',            'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '42',   'nama_akun' => 'Retur Penjualan',                                 'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '43',   'nama_akun' => 'Pendapatan Lain-Lain',                            'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            
            // ==========================================
            // BIAYA PRODUKSI (5xxx)
            // ==========================================
            
            // BBB - Biaya Bahan Baku (AYAM)
            ['kode_akun' => '51',   'nama_akun' => 'BBB - Biaya Bahan Baku',                          'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '510',  'nama_akun' => 'BBB - Ayam Potong',                               'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '511',  'nama_akun' => 'BBB - Ayam Kampung',                              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '512',  'nama_akun' => 'BBB - Bebek',                                     'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            
            // BTKL - Biaya Tenaga Kerja Langsung
            ['kode_akun' => '52',   'nama_akun' => 'BTKL - Biaya Tenaga Kerja Langsung',              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '520',  'nama_akun' => 'BTKL - Perbumbuan',                               'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '521',  'nama_akun' => 'BTKL - Penggorengan',                             'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '522',  'nama_akun' => 'BTKL - Pengemasan',                               'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            
            // BOP - Biaya Overhead Pabrik
            ['kode_akun' => '53',   'nama_akun' => 'BOP - Biaya Overhead Pabrik',                     'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '530',  'nama_akun' => 'BOP - Biaya Bahan Baku Tidak Langsung',           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '531',  'nama_akun' => 'BOP - Air',                                       'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '532',  'nama_akun' => 'BOP - Minyak Goreng',                             'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '533',  'nama_akun' => 'BOP - Tepung Terigu',                             'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '534',  'nama_akun' => 'BOP - Tepung Maizena',                            'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '535',  'nama_akun' => 'BOP - Lada',                                      'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '536',  'nama_akun' => 'BOP - Bubuk Kaldu',                               'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '537',  'nama_akun' => 'BOP - Bubuk Bawang Putih',                        'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '538',  'nama_akun' => 'BOP - Kemasan',                                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            
            // BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung
            ['kode_akun' => '54',   'nama_akun' => 'BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung',   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            
            // BOP - Lainnya
            ['kode_akun' => '55',   'nama_akun' => 'BOP - Lainnya',                                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '550',  'nama_akun' => 'BOP - Beban Listrik',                             'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '551',  'nama_akun' => 'BOP - Beban Sewa Tempat',                         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '552',  'nama_akun' => 'BOP - Beban Penyusutan Gedung',                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '553',  'nama_akun' => 'BOP - Beban Penyusutan Peralatan',                'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '554',  'nama_akun' => 'BOP - Beban Penyusutan Kendaraan',                'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '555',  'nama_akun' => 'BOP - Beban Penyusutan Mesin',                    'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '556',  'nama_akun' => 'BOP - Beban Air',                                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '557',  'nama_akun' => 'BOP - Lainnya',                                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '558',  'nama_akun' => 'Beban Transport Pembelian',                       'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '559',  'nama_akun' => 'Diskon Pembelian',                                'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            
            // Harga Pokok Penjualan
            ['kode_akun' => '56',   'nama_akun' => 'Harga Pokok Penjualan',                           'tipe_akun' => 'Beban',      'saldo_normal' => 'debit'],
        ];

        // Insert data dengan user_id dari auth atau null untuk template
        foreach ($coas as $coa) {
            DB::table('coas')->insert([
                'user_id' => null, // NULL = template global, atau ganti dengan auth()->id() untuk user-specific
                'company_id' => null,
                'kode_akun' => $coa['kode_akun'],
                'nama_akun' => $coa['nama_akun'],
                'tipe_akun' => $coa['tipe_akun'],
                'kategori_akun' => '-', // Default: '-' karena NOT NULL
                'is_akun_header' => 0,
                'kode_induk' => null,
                'saldo_normal' => $coa['saldo_normal'],
                'saldo_awal' => 0.00,
                'tanggal_saldo_awal' => null,
                'posted_saldo_awal' => 0,
                'keterangan' => null,
                'nomor_rekening' => null,
                'atas_nama' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('✅ COA Ayam seeder completed successfully!');
        $this->command->info('📊 Total COA created: ' . count($coas));
    }
}

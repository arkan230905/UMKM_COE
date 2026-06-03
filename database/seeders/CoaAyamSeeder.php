<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoaAyamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder COA untuk usaha Ayam Crispy
     * 
     * CATATAN: 
     * - Seeder ini akan memasukkan COA untuk user dan company yang sedang aktif
     * - Jika tidak ada user/company, COA akan dibuat dengan user_id dan company_id = null
     * - Unique constraint: kode_akun + company_id harus unik
     */
    public function run(): void
    {
        $now = now();
        
        // Ambil user_id dan company_id dari user yang sedang login
        $userId = auth()->check() ? auth()->id() : null;
        $companyId = null;
        
        // Coba ambil company_id dari user yang login
        if ($userId) {
            $user = \App\Models\User::find($userId);
            $companyId = $user->company_id ?? null;
        }

        $coas = [
            // ASET
            ['kode_akun' => '11',   'nama_akun' => 'Aset',                                                'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '111',  'nama_akun' => 'Kas Bank',                                            'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '112',  'nama_akun' => 'Kas',                                                 'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '113',  'nama_akun' => 'Kas Kecil',                                           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Baku
            ['kode_akun' => '114',  'nama_akun' => 'Pers. Bahan Baku',                                    'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1141', 'nama_akun' => 'Pers. Bahan Baku Ayam Potong',                        'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1142', 'nama_akun' => 'Pers. Bahan Baku Ayam Kampung',                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1143', 'nama_akun' => 'Pers. Bahan Baku Bebek',                              'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1144', 'nama_akun' => 'Pers. Bahan Baku Ayam Lainnya',                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Persediaan Bahan Pendukung
            ['kode_akun' => '115',  'nama_akun' => 'Pers. Bahan Pendukung',                               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1150', 'nama_akun' => 'Pers. Bahan Pendukung Air',                           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1151', 'nama_akun' => 'Pers. Bahan Pendukung Minyak Goreng',                 'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1152', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Terigu',                 'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1153', 'nama_akun' => 'Pers. Bahan Pendukung Tepung Maizena',                'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1154', 'nama_akun' => 'Pers. Bahan Pendukung Lada',                          'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1155', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Kaldu',                   'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1156', 'nama_akun' => 'Pers. Bahan Pendukung Bubuk Bawang Putih',            'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1157', 'nama_akun' => 'Pers. Bahan Pendukung Kemasan',                       'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Persediaan Barang Jadi
            ['kode_akun' => '116',  'nama_akun' => 'Pers. Barang Jadi',                                   'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1161', 'nama_akun' => 'Pers. Barang Jadi Ayam Crispy Macdi',                 'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '1162', 'nama_akun' => 'Pers. Barang Jadi Ayam Goreng Bundo',                 'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Persediaan Barang dalam Proses
            ['kode_akun' => '117',  'nama_akun' => 'Pers. Barang dalam Proses',                           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            
            // Aset Lainnya
            ['kode_akun' => '118',  'nama_akun' => 'Piutang',                                             'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '119',  'nama_akun' => 'Peralatan',                                           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '120',  'nama_akun' => 'Akumulasi Penyusutan Peralatan',                      'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '121',  'nama_akun' => 'Gedung',                                              'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '122',  'nama_akun' => 'Akumulasi Penyusutan Gedung',                         'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '123',  'nama_akun' => 'Kendaraan',                                           'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '124',  'nama_akun' => 'Akumulasi Penyusutan Kendaraan',                      'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '125',  'nama_akun' => 'Mesin',                                               'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '126',  'nama_akun' => 'Akumulasi Penyusutan Mesin',                          'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],
            ['kode_akun' => '127',  'nama_akun' => 'PPN Masukkan',                                        'tipe_akun' => 'Aset',       'saldo_normal' => 'debit'],

            // KEWAJIBAN
            ['kode_akun' => '21',   'nama_akun' => 'Hutang',                                              'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            ['kode_akun' => '210',  'nama_akun' => 'Hutang Usaha',                                        'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            ['kode_akun' => '211',  'nama_akun' => 'Hutang Gaji',                                         'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],
            ['kode_akun' => '212',  'nama_akun' => 'PPN Keluaran',                                        'tipe_akun' => 'Kewajiban',  'saldo_normal' => 'kredit'],

            // MODAL
            ['kode_akun' => '31',   'nama_akun' => 'Modal',                                               'tipe_akun' => 'Modal',      'saldo_normal' => 'kredit'],
            ['kode_akun' => '310',  'nama_akun' => 'Modal Usaha',                                         'tipe_akun' => 'Modal',      'saldo_normal' => 'kredit'],
            ['kode_akun' => '311',  'nama_akun' => 'Prive',                                               'tipe_akun' => 'Modal',      'saldo_normal' => 'kredit'],

            // PENDAPATAN
            ['kode_akun' => '41',   'nama_akun' => 'Penjualan',                                           'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '410',  'nama_akun' => 'Penjualan - Produk Ayam Crispy Macdi',                'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '411',  'nama_akun' => 'Penjualan - Produk Ayam Goreng Bundo',                'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '42',   'nama_akun' => 'Retur Penjualan',                                     'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
            ['kode_akun' => '43',   'nama_akun' => 'Pendapatan Lain-Lain',                                'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],

            // BIAYA BAHAN BAKU (BBB)
            ['kode_akun' => '51',   'nama_akun' => 'BBB - Biaya Bahan Baku',                              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '510',  'nama_akun' => 'BBB - Ayam Potong',                                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '511',  'nama_akun' => 'BBB - Ayam Kampung',                                  'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '512',  'nama_akun' => 'BBB - Bebek',                                         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],

            // BIAYA TENAGA KERJA LANGSUNG (BTKL)
            ['kode_akun' => '52',   'nama_akun' => 'BTKL - Biaya Tenaga Kerja Langsung',                  'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '520',  'nama_akun' => 'BTKL - Perbumbuan',                                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '521',  'nama_akun' => 'BTKL - Penggorengan',                                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '522',  'nama_akun' => 'BTKL - Pengemasan',                                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],

            // BIAYA OVERHEAD PABRIK (BOP)
            ['kode_akun' => '53',   'nama_akun' => 'BOP - Biaya Overhead Pabrik',                         'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '530',  'nama_akun' => 'BOP - Biaya Bahan Baku Tidak Langsung',               'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '531',  'nama_akun' => 'BOP - Air',                                           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '532',  'nama_akun' => 'BOP - Minyak Goreng',                                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '533',  'nama_akun' => 'BOP - Tepung Terigu',                                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '534',  'nama_akun' => 'BOP - Tepung Maizena',                                'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '535',  'nama_akun' => 'BOP - Lada',                                          'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '536',  'nama_akun' => 'BOP - Bubuk Kaldu',                                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '537',  'nama_akun' => 'BOP - Bubuk Bawang Putih',                            'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '538',  'nama_akun' => 'BOP - Kemasan',                                       'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],

            // BIAYA TENAGA KERJA TIDAK LANGSUNG (BOP BTKTL)
            ['kode_akun' => '54',   'nama_akun' => 'BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung',       'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '540',  'nama_akun' => 'BOP BTKTL - Biaya Pegawai Pemasaran',                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '541',  'nama_akun' => 'BOP BTKTL - Biaya Pegawai Kemasan',                   'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '542',  'nama_akun' => 'BOP BTKTL - Biaya Satpam Pabrik',                     'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '543',  'nama_akun' => 'BOP BTKTL - Biaya Cleaning Service',                  'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '544',  'nama_akun' => 'BOP BTKTL - Biaya Mandor',                            'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '545',  'nama_akun' => 'BOP BTKTL - Biaya Pegawai Keuangan',                  'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '546',  'nama_akun' => 'BOP BTKTL - BTKTL Lainnya',                           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],

            // BOP TIDAK LANGSUNG LAINNYA (BOP TL)
            ['kode_akun' => '55',   'nama_akun' => 'BOP TL - BOP Tidak Langsung Lainnya',                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '550',  'nama_akun' => 'BOP TL - Biaya Listrik',                              'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '551',  'nama_akun' => 'BOP TL - Sewa Tempat',                                'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '552',  'nama_akun' => 'BOP TL - Biaya Penyusutan Gedung',                    'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '553',  'nama_akun' => 'BOP TL - Biaya Penyusutan Peralatan',                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '554',  'nama_akun' => 'BOP TL - Biaya Penyusutan Kendaraan',                 'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '555',  'nama_akun' => 'BOP TL - Biaya Penyusutan Mesin',                     'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '556',  'nama_akun' => 'BOP TL - Biaya Air',                                  'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '557',  'nama_akun' => 'BOP TL - Lainnya',                                    'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '558',  'nama_akun' => 'Beban Transport Pembelian',                           'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
            ['kode_akun' => '559',  'nama_akun' => 'Diskon Pembelian',                                    'tipe_akun' => 'Biaya',      'saldo_normal' => 'debit'],
        ];

        echo "=== COA AYAM SEEDER ===\n";
        echo "Total COA: " . count($coas) . "\n";
        
        if ($userId !== null) {
            $user = \App\Models\User::find($userId);
            echo "User: {$user->name} (ID: {$userId})\n";
            echo "Company ID: " . ($companyId ?? 'null') . "\n";
        } else {
            echo "User: Global/Default (user_id = null)\n";
            echo "Company ID: null\n";
        }
        echo "\n";

        try {
            DB::beginTransaction();

            $insertCount = 0;
            $updateCount = 0;

            foreach ($coas as $coa) {
                // Cek apakah COA sudah ada berdasarkan kode_akun, user_id, dan company_id
                $query = DB::table('coas')->where('kode_akun', $coa['kode_akun']);
                
                // Filter by company_id (ini yang paling penting karena unique constraint)
                if ($companyId !== null) {
                    $query->where('company_id', $companyId);
                } else {
                    $query->whereNull('company_id');
                }
                
                // Filter by user_id jika ada
                if ($userId !== null) {
                    $query->where('user_id', $userId);
                } else {
                    $query->whereNull('user_id');
                }
                
                $existing = $query->first();

                if ($existing) {
                    // Update jika sudah ada
                    DB::table('coas')
                        ->where('id', $existing->id)
                        ->update([
                            'nama_akun' => $coa['nama_akun'],
                            'tipe_akun' => $coa['tipe_akun'],
                            'saldo_normal' => $coa['saldo_normal'],
                            'updated_at' => $now
                        ]);
                    $updateCount++;
                    echo "✓ Updated: {$coa['kode_akun']} - {$coa['nama_akun']}\n";
                } else {
                    // Insert jika belum ada
                    DB::table('coas')->insert([
                        'kode_akun' => $coa['kode_akun'],
                        'nama_akun' => $coa['nama_akun'],
                        'tipe_akun' => $coa['tipe_akun'],
                        'saldo_normal' => $coa['saldo_normal'],
                        'saldo_awal' => 0,
                        'user_id' => $userId,
                        'company_id' => $companyId, // Tambahkan company_id
                        'created_at' => $now,
                        'updated_at' => $now
                    ]);
                    $insertCount++;
                    echo "✓ Inserted: {$coa['kode_akun']} - {$coa['nama_akun']}\n";
                }
            }

            DB::commit();

            echo "\n" . str_repeat("=", 50) . "\n";
            echo "🎉 SEEDER BERHASIL!\n";
            echo "📊 Ringkasan:\n";
            echo "   - COA Baru: {$insertCount}\n";
            echo "   - COA Diupdate: {$updateCount}\n";
            echo "   - Total: " . ($insertCount + $updateCount) . "\n";
            echo str_repeat("=", 50) . "\n";

        } catch (\Exception $e) {
            DB::rollback();
            
            echo "\n❌ ERROR!\n";
            echo "Pesan: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            
            throw $e;
        }
    }
}

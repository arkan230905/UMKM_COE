<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Coa;

class CoaSeeder extends Seeder
{
    public function run(): void
    {
        // COMPLETE COA LIST - Ayam Goreng Bundo
        // Format: [Nama Akun, Kode Akun, Tipe Akun, Saldo Normal]
        $coas = [
            // ASSET (11)
            ['ASSET', '11', 'Aset', 'debit'],
            ['Kas Bank', '111', 'Aset', 'debit'],
            ['Kas', '112', 'Aset', 'debit'],
            ['Kas Kecil', '113', 'Aset', 'debit'],
            
            // Persediaan Bahan Baku (114x)
            ['Pers. Bahan Baku', '114', 'Aset', 'debit'],
            ['Pers. Bahan Baku ayam potong', '1141', 'Aset', 'debit'],
            ['Pers. Bahan Baku ayam kampung', '1142', 'Aset', 'debit'],
            ['Pers. Bahan Baku bebek', '1143', 'Aset', 'debit'],
            ['Pers. Bahan Baku ayam lainnya', '1144', 'Aset', 'debit'],
            
            // Persediaan Bahan Pendukung (115x)
            ['Pers. Bahan Pendukung', '115', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Air', '1150', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Minyak Goreng', '1151', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Tepung Terigu', '1152', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Tepung Maizena', '1153', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Lada', '1154', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Bubuk Kaldu', '1155', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Bubuk Bawang Putih', '1156', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Kemasan', '1157', 'Aset', 'debit'],
            
            // Persediaan Barang Jadi (116x)
            ['Pers. Barang Jadi', '116', 'Aset', 'debit'],
            ['Pers. Barang Jadi Ayam Crispy Macdi', '1161', 'Aset', 'debit'],
            ['Pers. Barang Jadi Ayam Goreng Bundo', '1162', 'Aset', 'debit'],
            
            // Work In Progress (117x)
            ['Pers. Barang dalam Proses', '117', 'Aset', 'debit'],
            ['Pers. Barang Dalam Proses - BBB (WIP BBB)', '1171', 'Aset', 'debit'],
            ['Pers. Barang Dalam Proses - BTKL (WIP BTKL)', '1172', 'Aset', 'debit'],
            ['Pers. Barang Dalam Proses - BOP (WIP BOP)', '1173', 'Aset', 'debit'],
            
            // Aset Lainnya (118-127)
            ['Piutang', '118', 'Aset', 'debit'],
            ['Peralatan', '119', 'Aset', 'debit'],
            ['Akumulasi Penyusutan Peralatan', '120', 'Aset', 'debit'],
            ['Gedung', '121', 'Aset', 'debit'],
            ['Akumulasi Penyusutan Gedung', '122', 'Aset', 'debit'],
            ['Kendaraan', '123', 'Aset', 'debit'],
            ['Akumulasi Penyusutan Kendaraan', '124', 'Aset', 'debit'],
            ['Mesin', '125', 'Aset', 'debit'],
            ['Akumulasi Penyusutan Mesin', '126', 'Aset', 'debit'],
            ['PPN Masukkan', '127', 'Aset', 'debit'],
            
            // KEWAJIBAN (21x)
            ['Hutang', '21', 'Kewajiban', 'kredit'],
            ['Hutang Usaha', '210', 'Kewajiban', 'kredit'],
            ['Hutang Gaji', '211', 'Kewajiban', 'kredit'],
            ['PPN Keluaran', '212', 'Kewajiban', 'kredit'],
            
            // MODAL (31x)
            ['Modal', '31', 'Modal', 'kredit'],
            ['Modal Usaha', '310', 'Modal', 'kredit'],
            ['Prive', '311', 'Modal', 'kredit'],
            
            // PENDAPATAN (41x-43x)
            ['Penjualan', '41', 'Pendapatan', 'kredit'],
            ['Penjualan - Produk Ayam Crispy Macdi', '410', 'Pendapatan', 'kredit'],
            ['Penjualan - Produk Ayam Goreng Bundo', '411', 'Pendapatan', 'kredit'],
            ['Retur Penjualan', '42', 'Pendapatan', 'kredit'],
            ['Pendapatan Lain-Lain', '43', 'Pendapatan', 'kredit'],
            
            // BIAYA - BBB (51x)
            ['BBB-Biaya Bahan Baku', '51', 'Biaya', 'debit'],
            ['BBB-ayam potong', '510', 'Biaya', 'debit'],
            ['BBB-ayam kampung', '511', 'Biaya', 'debit'],
            ['BBB-bebek', '512', 'Biaya', 'debit'],
            
            // BIAYA - BTKL (52x)
            ['BTKL-Biaya Tenaga Kerja Langsung', '52', 'Biaya', 'debit'],
            ['BTKL-Perbumbuan', '520', 'Biaya', 'debit'],
            ['BTKL-Penggorengan', '521', 'Biaya', 'debit'],
            ['BTKL-Pengemasan', '522', 'Biaya', 'debit'],
            
            // BIAYA - BOP (53x)
            ['BOP-Biaya Overhead Pabrik', '53', 'Biaya', 'debit'],
            ['BOP-Biaya Bahan Baku Tidak Langsung', '530', 'Biaya', 'debit'],
            ['BOP-Air', '531', 'Biaya', 'debit'],
            ['BOP-Minyak Goreng', '532', 'Biaya', 'debit'],
            ['BOP-Tepung Terigu', '533', 'Biaya', 'debit'],
            ['BOP-Tepung Maizena', '534', 'Biaya', 'debit'],
            ['BOP- Lada', '535', 'Biaya', 'debit'],
            ['BOP- Bubuk Kaldu', '536', 'Biaya', 'debit'],
            ['BOP- Bubuk Bawang Putih', '537', 'Biaya', 'debit'],
            ['BOP-Kemasan', '538', 'Biaya', 'debit'],
            
            // BIAYA - BOP BTKTL (54x)
            ['BOP BTKTL-Biaya Tenaga Kerja Tidak Langsung', '54', 'Biaya', 'debit'],
            ['BOP BTKTL - Biaya Pegawai Pemasaran', '540', 'Biaya', 'debit'],
            ['BOP BTKTL - Biaya Pegawai Kemasan', '541', 'Biaya', 'debit'],
            ['BOP BTKTL - Biaya Satpam Pabrik', '542', 'Biaya', 'debit'],
            ['BOP BTKTL - Biaya Cleaning Service', '543', 'Biaya', 'debit'],
            ['BOP BTKTL - Biaya Mandor', '544', 'Biaya', 'debit'],
            ['BOP BTKTL - Biaya Pegawai Keuangan', '545', 'Biaya', 'debit'],
            ['BOP BTKTL - BTKTL Lainnya', '546', 'Biaya', 'debit'],
            
            // BIAYA - BOP TL (55x)
            ['BOP TL - BOP Tidak Langsung Lainnya', '55', 'Biaya', 'debit'],
            ['BOP TL - Biaya Listrik', '550', 'Biaya', 'debit'],
            ['BOP TL - Sewa Tempat', '551', 'Biaya', 'debit'],
            ['BOP TL - Biaya Penyusutan Gedung', '552', 'Biaya', 'debit'],
            ['BOP TL - Biaya Penyusutan Peralatan', '553', 'Biaya', 'debit'],
            ['BOP TL - Biaya Penyusutan Kendaraan', '554', 'Biaya', 'debit'],
            ['BOP TL - Biaya Penyusutan Mesin', '555', 'Biaya', 'debit'],
            ['BOP TL - Biaya Air', '556', 'Biaya', 'debit'],
            ['BOP TL - Lainnya', '557', 'Biaya', 'debit'],
            ['Beban Transport Pembelian', '558', 'Biaya', 'debit'],
            ['Diskon Pembelian', '559', 'Biaya', 'debit'],
        ];

        // Get all users (for multi-tenant support)
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please create users first.');
            return;
        }

        $this->command->info("========================================");
        $this->command->info("COA SEEDER - AYAM GORENG BUNDO");
        $this->command->info("Total COAs: " . count($coas));
        $this->command->info("Akan menambahkan COA untuk semua user");
        $this->command->info("========================================\n");

        foreach ($users as $user) {
            $this->command->info("Processing user: {$user->name} (ID: {$user->id})");
            
            $added = 0;
            $updated = 0;
            
            foreach ($coas as $item) {
                // Check if COA already exists for this user
                $existing = Coa::where('kode_akun', $item[1])
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($existing) {
                    // Update existing COA
                    $existing->update([
                        'nama_akun' => $item[0],
                        'tipe_akun' => $item[2],
                        'saldo_normal' => $item[3],
                        'updated_at' => now(),
                    ]);
                    $updated++;
                } else {
                    // Insert new COA
                    Coa::create([
                        'nama_akun' => $item[0],
                        'kode_akun' => $item[1],
                        'tipe_akun' => $item[2],
                        'saldo_normal' => $item[3],
                        'saldo_awal' => 0,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $added++;
                }
            }
            
            $this->command->info("  ✅ {$added} COA ditambahkan, {$updated} COA diupdate\n");
        }

        $this->command->info("========================================");
        $this->command->info("✅ COA Seeder completed!");
        $this->command->info("Total users processed: " . $users->count());
        $this->command->info("Total COAs per user: " . count($coas));
        $this->command->info("========================================");
    }
}

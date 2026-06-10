<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Coa;

class ReplaceCoaWithJasukeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("========================================");
        $this->command->info("REPLACE COA WITH JASUKE COA");
        $this->command->info("========================================\n");

        // COMPLETE COA LIST - Jasuke (Jagung Susu Keju)
        $coas = [
            // ASET
            ['Aset', '11', 'Aset', 'debit'],
            ['Kas Bank', '111', 'Aset', 'debit'],
            ['Kas', '112', 'Aset', 'debit'],
            ['Kas Kecil', '113', 'Aset', 'debit'],
            ['Pers. Bahan Baku', '114', 'Aset', 'debit'],
            ['Pers. Bahan Baku Jagung', '1141', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung', '115', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Susu', '1151', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Keju', '1152', 'Aset', 'debit'],
            ['Pers. Bahan Pendukung Kemasan (Cup)', '1153', 'Aset', 'debit'],
            ['Pers. Barang Jadi', '116', 'Aset', 'debit'],
            ['Pers. Barang Jadi Jasuke', '1161', 'Aset', 'debit'],
            ['Pers. Barang dalam Proses', '117', 'Aset', 'debit'],
            ['Pers. Barang Dalam Proses - BBB', '1171', 'Aset', 'debit'],
            ['Pers. Barang Dalam Proses - BTKL', '1172', 'Aset', 'debit'],
            ['Pers. Barang Dalam Proses - BOP', '1173', 'Aset', 'debit'],
            ['Piutang', '118', 'Aset', 'debit'],
            ['Peralatan', '119', 'Aset', 'debit'],
            ['Akumulasi Penyusutan Peralatan', '120', 'Aset', 'debit'],
            ['Mesin', '125', 'Aset', 'debit'],
            ['Akumulasi Penyusutan Mesin', '126', 'Aset', 'debit'],
            ['PPN Masukkan', '127', 'Aset', 'debit'],
            
            // KEWAJIBAN
            ['Hutang', '21', 'Kewajiban', 'kredit'],
            ['Hutang Usaha', '210', 'Kewajiban', 'kredit'],
            ['Hutang Gaji', '211', 'Kewajiban', 'kredit'],
            ['PPN Keluaran', '212', 'Kewajiban', 'kredit'],
            
            // EKUITAS/MODAL
            ['Modal', '31', 'Modal', 'kredit'],
            ['Modal Usaha', '310', 'Modal', 'kredit'],
            ['Prive', '311', 'Modal', 'kredit'],
            
            // PENDAPATAN
            ['Penjualan', '41', 'Pendapatan', 'kredit'],
            ['Penjualan - Jasuke', '410', 'Pendapatan', 'kredit'],
            ['Retur Penjualan', '42', 'Pendapatan', 'kredit'],
            
            // BIAYA
            ['BBB - Biaya Bahan Baku', '51', 'Biaya', 'debit'],
            ['BBB - Jagung', '510', 'Biaya', 'debit'],
            ['Beban Tunjangan', '513', 'Biaya', 'debit'],
            ['Beban Asuransi', '514', 'Biaya', 'debit'],
            ['Beban Bonus', '515', 'Biaya', 'debit'],
            ['Potongan Gaji', '516', 'Biaya', 'debit'],
            ['BTKL', '52', 'Biaya', 'debit'],
            ['BTKL - Produksi Jasuke', '520', 'Biaya', 'debit'],
            ['BOP', '53', 'Biaya', 'debit'],
            ['BOP - Susu', '530', 'Biaya', 'debit'],
            ['BOP - Keju', '531', 'Biaya', 'debit'],
            ['BOP - Kemasan', '532', 'Biaya', 'debit'],
            ['Beban Sewa', '54', 'Biaya', 'debit'],
            ['BOP Lain', '55', 'Biaya', 'debit'],
            ['BOP - Listrik', '550', 'Biaya', 'debit'],
            ['BOP - Air', '551', 'Biaya', 'debit'],
            ['BOP - Gas', '552', 'Biaya', 'debit'],
            ['BOP - Penyusutan Peralatan', '553', 'Biaya', 'debit'],
        ];

        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No users found!');
            return;
        }

        foreach ($users as $user) {
            $this->command->info("Processing user: {$user->name} (ID: {$user->id})");
            
            // Count existing COA
            $oldCoaCount = Coa::where('user_id', $user->id)->count();
            $this->command->warn("  Current COA count: {$oldCoaCount}");
            
            // Ask for confirmation (optional - comment out if want auto)
            // if (!$this->command->confirm("  Delete all COA and replace with Jasuke COA?", false)) {
            //     $this->command->info("  ⏭️  Skipped\n");
            //     continue;
            // }
            
            DB::beginTransaction();
            
            try {
                // Step 1: DELETE all existing COA for this user
                $deleted = Coa::where('user_id', $user->id)->delete();
                $this->command->info("  🗑️  Deleted {$deleted} old COA");
                
                // Step 2: INSERT new Jasuke COA
                $inserted = 0;
                foreach ($coas as $item) {
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
                    $inserted++;
                }
                
                DB::commit();
                
                $this->command->info("  ✅ Inserted {$inserted} new Jasuke COA");
                $this->command->info("  ✅ Success!\n");
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("  ❌ Error: " . $e->getMessage() . "\n");
            }
        }

        $this->command->info("========================================");
        $this->command->info("✅ Replace COA completed!");
        $this->command->info("Total users processed: " . $users->count());
        $this->command->info("Total COAs per user: " . count($coas));
        $this->command->info("========================================");
    }
}

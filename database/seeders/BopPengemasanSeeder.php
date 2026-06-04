<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BopProses;

class BopPengemasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = \App\Models\User::all();

        foreach ($users as $user) {
            // Data BOP Components for Pengemasan
            $komponenBop = [
                [
                    'component' => 'Listrik',
                    'rate_per_hour' => 6.00,
                    'description' => 'Listrik Mesin',
                    'coa_debit' => '1173',
                    'coa_kredit' => '510',
                ],
                [
                    'component' => 'Penyusutan Alat',
                    'rate_per_hour' => 3.00,
                    'description' => 'Alat Packing',
                    'coa_debit' => '1173',
                    'coa_kredit' => '510',
                ],
                [
                    'component' => 'Kemasan',
                    'rate_per_hour' => 2000.00,
                    'description' => 'Penunjang',
                    'coa_debit' => '1173',
                    'coa_kredit' => '510',
                ],
                [
                    'component' => 'Kebersihan',
                    'rate_per_hour' => 10.00,
                    'description' => 'Area',
                    'coa_debit' => '1173',
                    'coa_kredit' => '510',
                ],
                [
                    'component' => 'BTKL',
                    'rate_per_hour' => 226.00,
                    'description' => 'Pembebanan BTKL',
                    'coa_debit' => '1173',
                    'coa_kredit' => '510',
                ],
            ];

            // Calculate total BOP
            $totalBop = 0;
            foreach ($komponenBop as $comp) {
                $totalBop += round((float)$comp['rate_per_hour'], 2);
            }
            $totalBop = round($totalBop, 2);

            // Create or update BOP Proses for "Pengemasan"
            $bopProses = BopProses::where('user_id', $user->id)
                ->where('nama_bop_proses', 'Pengemasan')
                ->first();

            if ($bopProses) {
                // Update existing
                $bopProses->update([
                    'komponen_bop' => $komponenBop,
                    'total_bop_per_jam' => $totalBop,
                    'total_bop_per_produk' => $totalBop,
                    'bop_per_unit' => $totalBop,
                    'total_biaya_per_produk' => $totalBop,
                    'kapasitas_per_jam' => 1,
                    'keterangan' => 'BOP Proses Pengemasan',
                    'is_active' => true,
                ]);
                $this->command->info("✏️  BOP Pengemasan updated untuk user {$user->id}");
            } else {
                // Create new
                BopProses::create([
                    'user_id' => $user->id,
                    'nama_bop_proses' => 'Pengemasan',
                    'komponen_bop' => $komponenBop,
                    'total_bop_per_jam' => $totalBop,
                    'total_bop_per_produk' => $totalBop,
                    'kapasitas_per_jam' => 1,
                    'bop_per_unit' => $totalBop,
                    'total_biaya_per_produk' => $totalBop,
                    'keterangan' => 'BOP Proses Pengemasan',
                    'is_active' => true,
                ]);
                $this->command->info("✅ BOP Pengemasan created untuk user {$user->id} - Total: Rp " . number_format($totalBop, 2, ',', '.'));
            }
        }

        $this->command->info("✅ BOP Pengemasan seeding completed!");
    }
}

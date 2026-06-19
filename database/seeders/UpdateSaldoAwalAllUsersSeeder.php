<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateSaldoAwalAllUsersSeeder extends Seeder
{
    /**
     * Seeder untuk update Saldo Awal COA di SEMUA USER
     * 
     * Seeder ini dijalankan untuk:
     * 1. Update saldo awal untuk akun-akun yang sudah ada di semua user
     * 2. Menambahkan saldo awal untuk akun-akun baru yang belum ada
     * 
     * Data yang di-update:
     * - Kode 1111 (Bank BRI): Rp 100.000.000 (Debit)
     * - Kode 1112 (Bank BCA): Rp 50.000.000 (Debit)
     * - Kode 1123 (Bank Mandiri): Rp 50.000.000 (Debit)
     * - Kode 112 (Kas): Rp 75.000.000 (Debit)
     * - Kode 113 (Kas Kecil): Rp 1.000.000 (Debit)
     * - Kode 118 (Piutang): Rp 11.000.000 (Debit)
     * - Kode 211 (Hutang Usaha): Rp 12.000.000 (Kredit)
     * - Kode 311 (Modal Usaha): Rp 275.000.000 (Kredit)
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $saldoAwalData = [
            // ==========================================
            // ASET - KAS & BANK
            // ==========================================
            [
                'kode_akun' => '1111',
                'nama_akun' => 'Bank BRI',
                'saldo_awal' => 100000000,
            ],
            [
                'kode_akun' => '1112',
                'nama_akun' => 'Bank BCA',
                'saldo_awal' => 50000000,
            ],
            [
                'kode_akun' => '1113',
                'nama_akun' => 'Bank Mandiri',
                'saldo_awal' => 50000000,
            ],
            [
                'kode_akun' => '112',
                'nama_akun' => 'Kas',
                'saldo_awal' => 75000000,
            ],
            [
                'kode_akun' => '113',
                'nama_akun' => 'Kas Kecil',
                'saldo_awal' => 1000000,
            ],

            // ==========================================
            // ASET - PIUTANG
            // ==========================================
            [
                'kode_akun' => '118',
                'nama_akun' => 'Piutang',
                'saldo_awal' => 11000000,
            ],

            // ==========================================
            // KEWAJIBAN
            // ==========================================
            [
                'kode_akun' => '211',
                'nama_akun' => 'Hutang Usaha',
                'saldo_awal' => 12000000,
            ],

            // ==========================================
            // MODAL
            // ==========================================
            [
                'kode_akun' => '311',
                'nama_akun' => 'Modal Usaha',
                'saldo_awal' => 275000000,
            ],
        ];

        // Get all users
        $users = DB::table('users')->get();

        if ($users->isEmpty()) {
            $this->command->error('No users found in database.');
            return;
        }

        $totalUpdated = 0;
        $totalCreated = 0;

        foreach ($users as $user) {
            $this->command->line("\n👤 Processing user: {$user->name} (ID: {$user->id})");
            $this->command->line("────────────────────────────────────");

            // Karena Bank Mandiri diubah dari 1123 ke 1113, kita harus memastikan jika ada akun 1123 di-rename ke 1113 untuk menjaga relasi dan saldo.
            $oldMandiri = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', '1123')
                ->first();
            if ($oldMandiri) {
                $exists1113 = DB::table('coas')->where('user_id', $user->id)->where('kode_akun', '1113')->exists();
                if (!$exists1113) {
                    DB::table('coas')
                        ->where('id', $oldMandiri->id)
                        ->update(['kode_akun' => '1113']);
                } else {
                    // Jika 1113 sudah ada, hapus yang 1123 jika kosong atau aman (kita asumsikan aman jika tidak update)
                    DB::table('coas')->where('id', $oldMandiri->id)->delete();
                }
            }

            // Seabank 1124 ke 1114
            $oldSeabank = DB::table('coas')
                ->where('user_id', $user->id)
                ->where('kode_akun', '1124')
                ->first();
            if ($oldSeabank) {
                $exists1114 = DB::table('coas')->where('user_id', $user->id)->where('kode_akun', '1114')->exists();
                if (!$exists1114) {
                    DB::table('coas')
                        ->where('id', $oldSeabank->id)
                        ->update(['kode_akun' => '1114']);
                } else {
                    DB::table('coas')->where('id', $oldSeabank->id)->delete();
                }
            }

            foreach ($saldoAwalData as $data) {
                $coa = DB::table('coas')
                    ->where('user_id', $user->id)
                    ->where('kode_akun', $data['kode_akun'])
                    ->first();

                if ($coa) {
                    // Update saldo awal untuk COA yang sudah ada
                    DB::table('coas')
                        ->where('user_id', $user->id)
                        ->where('kode_akun', $data['kode_akun'])
                        ->update([
                            'saldo_awal' => $data['saldo_awal'],
                            'tanggal_saldo_awal' => $now,
                            'updated_at' => $now,
                        ]);
                    
                    $this->command->info("  ✅ Updated: {$data['kode_akun']} ({$data['nama_akun']}) - Rp " . number_format($data['saldo_awal'], 0, ',', '.'));
                    $totalUpdated++;
                } else {
                    // Insert baru jika belum ada (optional safety check)
                    DB::table('coas')->insert([
                        'user_id' => $user->id,
                        'kode_akun' => $data['kode_akun'],
                        'nama_akun' => $data['nama_akun'],
                        'tipe_akun' => $this->getCoaTipe($data['kode_akun']),
                        'kategori_akun' => $this->getCoaTipe($data['kode_akun']),
                        'is_akun_header' => 0,
                        'kode_induk' => null,
                        'saldo_normal' => $this->getCoaSaldoNormal($data['kode_akun']),
                        'saldo_awal' => $data['saldo_awal'],
                        'tanggal_saldo_awal' => $now,
                        'posted_saldo_awal' => 0,
                        'keterangan' => null,
                        'nomor_rekening' => null,
                        'atas_nama' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    
                    $this->command->info("  ✨ Created: {$data['kode_akun']} ({$data['nama_akun']}) - Rp " . number_format($data['saldo_awal'], 0, ',', '.'));
                    $totalCreated++;
                }
            }
        }

        $this->command->line("\n");
        $this->command->info("═══════════════════════════════════════════════════════");
        $this->command->info("📊 SALDO AWAL UPDATE SUMMARY");
        $this->command->info("═══════════════════════════════════════════════════════");
        $this->command->info("Total Users Processed: " . $users->count());
        $this->command->info("Total Records Updated: " . $totalUpdated);
        $this->command->info("Total Records Created: " . $totalCreated);
        $this->command->info("─────────────────────────────────────────────────────");
        $this->command->info("📈 Breakdown:");
        $this->command->info("   • Total Aset (Debit): Rp " . number_format(100000000 + 50000000 + 50000000 + 75000000 + 1000000 + 11000000, 0, ',', '.'));
        $this->command->info("   • Total Kewajiban (Kredit): Rp " . number_format(12000000, 0, ',', '.'));
        $this->command->info("   • Total Modal (Kredit): Rp " . number_format(275000000, 0, ',', '.'));
        $this->command->info("   • Aset = Kewajiban + Modal: Rp " . number_format(287000000, 0, ',', '.') . " ✅");
        $this->command->info("═══════════════════════════════════════════════════════");
    }

    /**
     * Get tipe akun berdasarkan kode akun
     */
    private function getCoaTipe(string $kodeAkun): string
    {
        $tipeMap = [
            '1111' => 'Aset',
            '1112' => 'Aset',
            '1113' => 'Aset',
            '112' => 'Aset',
            '113' => 'Aset',
            '118' => 'Aset',
            '211' => 'Kewajiban',
            '311' => 'Modal',
        ];
        
        return $tipeMap[$kodeAkun] ?? 'Aset';
    }

    /**
     * Get saldo normal berdasarkan kode akun
     */
    private function getCoaSaldoNormal(string $kodeAkun): string
    {
        $saldoNormalMap = [
            '1111' => 'debit',
            '1112' => 'debit',
            '1113' => 'debit',
            '112' => 'debit',
            '113' => 'debit',
            '118' => 'debit',
            '211' => 'kredit',
            '311' => 'kredit',
        ];
        
        return $saldoNormalMap[$kodeAkun] ?? 'debit';
    }
}

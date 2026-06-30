<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPegawaiUserLinks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pegawai:fix-user-links
                            {--dry-run : Tampilkan rencana perubahan tanpa mengeksekusi UPDATE}';

    /**
     * The console command description.
     */
    protected $description = 'Perbaiki data pegawai yang corrupt: sinkronkan user_id, pegawai_id, dan perusahaan_id antara tabel users dan pegawais berdasarkan kecocokan email.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('⚠️  MODE DRY-RUN: Tidak ada data yang akan diubah.');
        } else {
            $this->info('🚀 Memulai proses perbaikan data pegawai...');
        }

        $this->newLine();

        // Ambil semua pegawai yang punya email
        $pegawais = DB::table('pegawais')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->get(['id', 'email', 'user_id', 'perusahaan_id', 'nama']);

        $this->info("📋 Total data pegawai yang ditemukan: {$pegawais->count()} record");
        $this->newLine();

        $fixedCount   = 0;
        $skippedCount = 0;
        $notFoundCount = 0;

        $rows = [];

        foreach ($pegawais as $pegawai) {
            // Cari user berdasarkan email yang sama
            $user = DB::table('users')
                ->where('email', $pegawai->email)
                ->first(['id', 'email', 'role', 'pegawai_id', 'perusahaan_id']);

            if (!$user) {
                $this->warn("  [SKIP - Tidak Ada User] Pegawai ID {$pegawai->id} ({$pegawai->nama}) - email: {$pegawai->email}");
                $notFoundCount++;
                continue;
            }

            // Tentukan perusahaan_id yang akan dipakai
            // Prioritas: dari pegawais (jika ada), atau dari user jika sudah punya
            $perusahaanId = $pegawai->perusahaan_id ?? $user->perusahaan_id ?? null;

            // Cek apakah semua sudah benar
            $pegawaiUserIdOk  = ($pegawai->user_id == $user->id);
            $userPegawaiIdOk  = ($user->pegawai_id == $pegawai->id);
            $userPerusahaanOk = ($user->perusahaan_id == $perusahaanId && $perusahaanId !== null);
            $userRoleOk       = ($user->role === 'pegawai');

            $allOk = $pegawaiUserIdOk && $userPegawaiIdOk && $userPerusahaanOk && $userRoleOk;

            if ($allOk) {
                $rows[] = [
                    $pegawai->id,
                    $pegawai->nama,
                    $pegawai->email,
                    '<fg=green>✅ Sudah benar</>',
                ];
                $skippedCount++;
                continue;
            }

            // Siapkan log perubahan
            $changes = [];
            if (!$pegawaiUserIdOk)  $changes[] = "pegawais.user_id: {$pegawai->user_id} → {$user->id}";
            if (!$userPegawaiIdOk)  $changes[] = "users.pegawai_id: {$user->pegawai_id} → {$pegawai->id}";
            if (!$userPerusahaanOk) $changes[] = "users.perusahaan_id: {$user->perusahaan_id} → {$perusahaanId}";
            if (!$userRoleOk)       $changes[] = "users.role: {$user->role} → pegawai";

            $rows[] = [
                $pegawai->id,
                $pegawai->nama,
                $pegawai->email,
                '<fg=yellow>⚠️  ' . implode(' | ', $changes) . '</>',
            ];

            if (!$isDryRun) {
                // Update pegawais.user_id
                DB::table('pegawais')
                    ->where('id', $pegawai->id)
                    ->update([
                        'user_id'       => $user->id,
                        'perusahaan_id' => $perusahaanId,
                        'updated_at'    => now(),
                    ]);

                // Update users: pegawai_id, perusahaan_id, role
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'pegawai_id'   => $pegawai->id,
                        'perusahaan_id' => $perusahaanId,
                        'role'         => 'pegawai',
                        'updated_at'   => now(),
                    ]);
            }

            $fixedCount++;
        }

        // Tampilkan tabel ringkasan
        $this->table(
            ['Pegawai ID', 'Nama', 'Email', 'Status / Perubahan'],
            $rows
        );

        $this->newLine();
        $this->info('📊 RINGKASAN:');
        $this->line("   ✅ Sudah benar (dilewati) : <fg=green>{$skippedCount}</>");
        $this->line("   ⚠️  Diperbaiki             : <fg=yellow>{$fixedCount}</>");
        $this->line("   ❌ User tidak ditemukan   : <fg=red>{$notFoundCount}</>");

        if ($isDryRun && $fixedCount > 0) {
            $this->newLine();
            $this->warn("💡 Jalankan tanpa --dry-run untuk mengeksekusi {$fixedCount} perubahan di atas:");
            $this->line('   php artisan pegawai:fix-user-links');
        } elseif (!$isDryRun && $fixedCount > 0) {
            $this->newLine();
            $this->info("✅ Berhasil memperbaiki {$fixedCount} record pegawai.");
        } elseif ($fixedCount === 0 && $skippedCount > 0) {
            $this->newLine();
            $this->info('✅ Semua data sudah dalam kondisi benar. Tidak ada yang perlu diperbaiki.');
        }

        return self::SUCCESS;
    }
}

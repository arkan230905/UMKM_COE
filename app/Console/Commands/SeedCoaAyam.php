<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SeedCoaAyam extends Command
{
    protected $signature = 'coa:seed-ayam 
                            {--user-id= : ID user yang akan digunakan}
                            {--company-id= : ID company yang akan digunakan}
                            {--clean : Hapus COA lama sebelum insert}';

    protected $description = 'Seed COA untuk usaha Ayam Crispy dengan opsi user dan company';

    public function handle()
    {
        $userId = $this->option('user-id');
        $companyId = $this->option('company-id');
        $clean = $this->option('clean');

        // Jika tidak ada user-id, tanyakan
        if (!$userId) {
            $users = User::all(['id', 'name', 'email']);
            
            if ($users->isEmpty()) {
                $this->error('Tidak ada user di database!');
                return 1;
            }

            $this->info('Pilih user:');
            foreach ($users as $user) {
                $this->line("{$user->id}. {$user->name} ({$user->email})");
            }

            $userId = (int) $this->ask('Masukkan ID user');
        }

        $user = User::find((int) $userId);
        if (!$user) {
            $this->error("User dengan ID {$userId} tidak ditemukan!");
            return 1;
        }

        // Jika tidak ada company-id, ambil dari user
        if (!$companyId) {
            $companyId = $user->company_id;
        }

        $this->info("User: {$user->name} (ID: {$userId})");
        $this->info("Company ID: " . ($companyId ?? 'null'));

        // Konfirmasi jika clean
        if ($clean) {
            $count = DB::table('coas')
                ->where('user_id', $userId)
                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->count();

            if ($count > 0) {
                if (!$this->confirm("Akan menghapus {$count} COA lama. Lanjutkan?")) {
                    $this->info('Dibatalkan.');
                    return 0;
                }

                DB::table('coas')
                    ->where('user_id', $userId)
                    ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                    ->delete();

                $this->info("✓ {$count} COA lama berhasil dihapus.");
            }
        }

        // Login sebagai user ini untuk seeder
        auth()->login($user);

        // Jalankan seeder
        $this->info("\nMenjalankan seeder...\n");
        
        $seeder = new \Database\Seeders\CoaAyamSeeder();
        $seeder->run();

        $this->newLine();
        $this->info('✓ Seeder selesai!');

        return 0;
    }
}

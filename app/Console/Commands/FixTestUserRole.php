<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixTestUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-test-user-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix test user role dari pegawai menjadi admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::where('email', 'admin@umkm.test')->first();
        
        if (!$user) {
            $this->error('User admin@umkm.test tidak ditemukan');
            return;
        }
        
        $this->info("Found user: {$user->name} ({$user->email})");
        $this->info("Current role: {$user->role}");
        $this->info("Pegawai ID: {$user->pegawai_id}");
        $this->info("Perusahaan ID: {$user->perusahaan_id}");
        
        // Ubah role menjadi admin
        $user->update(['role' => 'admin']);
        
        $this->info("✅ Role updated to admin");
    }
}

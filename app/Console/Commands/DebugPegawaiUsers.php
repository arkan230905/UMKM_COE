<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DebugPegawaiUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-pegawai-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== DEBUG PEGAWAI USERS ===');
        
        // Get all users with role pegawai
        $pegawaiUsers = \App\Models\User::where('role', 'pegawai')->get();
        
        $this->info("\nTotal pegawai users: " . $pegawaiUsers->count());
        
        foreach ($pegawaiUsers as $user) {
            $this->line("\n--- User ID: {$user->id} ---");
            $this->line("Email: {$user->email}");
            $this->line("Name: {$user->name}");
            $this->line("Role: {$user->role}");
            $this->line("Pegawai ID (column): {$user->pegawai_id}");
            $this->line("Perusahaan ID (column): {$user->perusahaan_id}");
            
            // Try to get pegawai relationship
            try {
                $pegawai = $user->pegawai;
                if ($pegawai) {
                    $this->line("✅ Pegawai found: {$pegawai->nama} (ID: {$pegawai->id})");
                    $this->line("   Pegawai Perusahaan ID: {$pegawai->perusahaan_id}");
                } else {
                    $this->line("❌ Pegawai relationship is NULL");
                }
            } catch (\Exception $e) {
                $this->line("❌ Error loading pegawai: " . $e->getMessage());
            }
            
            // Check if pegawai exists in database
            $pegawaiDirect = \App\Models\Pegawai::where('user_id', $user->id)->first();
            if ($pegawaiDirect) {
                $this->line("✅ Pegawai found in DB: {$pegawaiDirect->nama} (ID: {$pegawaiDirect->id})");
            } else {
                $this->line("❌ No pegawai found in DB for this user");
            }
        }
        
        $this->info("\n=== END DEBUG ===");
    }
}

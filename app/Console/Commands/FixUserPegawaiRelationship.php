<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Pegawai;

class FixUserPegawaiRelationship extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-user-pegawai-relationship';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix relasi user-pegawai dengan mencocokkan email dan mengisi pegawai_id di tabel users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing user-pegawai relationship...');
        
        $fixed = 0;
        $errors = 0;
        
        // 1. Cari users dengan role pegawai yang belum punya pegawai_id
        $usersWithoutPegawai = User::where('role', 'pegawai')
            ->whereNull('pegawai_id')
            ->get();
        
        $this->info("Found {$usersWithoutPegawai->count()} users with role 'pegawai' without pegawai_id");
        
        foreach ($usersWithoutPegawai as $user) {
            // Cari pegawai berdasarkan email
            $pegawai = Pegawai::where('email', $user->email)->first();
            
            if ($pegawai) {
                $user->update(['pegawai_id' => $pegawai->id]);
                
                // Update pegawai dengan user_id dan perusahaan_id jika belum ada
                if (empty($pegawai->user_id) || empty($pegawai->perusahaan_id)) {
                    $pegawai->update([
                        'user_id' => $user->id,
                        'perusahaan_id' => $user->perusahaan_id
                    ]);
                }
                
                $this->line("  ✅ {$user->name} ({$user->email}) - Pegawai ID: {$pegawai->id} - Perusahaan ID: {$user->perusahaan_id}");
                $fixed++;
            } else {
                $this->error("  ❌ {$user->name} ({$user->email}) - Pegawai tidak ditemukan");
                $errors++;
            }
        }
        
        // 2. Cari pegawai yang belum punya user_id
        $pegawaiWithoutUser = Pegawai::whereNull('user_id')->get();
        
        $this->info("\nFound {$pegawaiWithoutUser->count()} pegawai without user_id");
        
        foreach ($pegawaiWithoutUser as $pegawai) {
            // Cari user berdasarkan email
            $user = User::where('email', $pegawai->email)->first();
            
            if ($user) {
                $pegawai->update(['user_id' => $user->id]);
                
                // Update user dengan pegawai_id jika belum ada
                if (empty($user->pegawai_id)) {
                    $user->update(['pegawai_id' => $pegawai->id]);
                }
                
                $this->line("  ✅ {$pegawai->nama} ({$pegawai->email}) - User ID: {$user->id}");
                $fixed++;
            } else {
                $this->error("  ❌ {$pegawai->nama} ({$pegawai->email}) - User tidak ditemukan");
                $errors++;
            }
        }
        
        $this->info("\n✅ Fixed: {$fixed}");
        $this->error("❌ Errors: {$errors}");
        
        if ($errors === 0) {
            $this->info("\n🎉 All user-pegawai relationships are now fixed!");
        }
    }
}

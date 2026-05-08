<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixPegawaiUserRelationship extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-pegawai-user-relationship';

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
        $this->info('=== FIX PEGAWAI USER RELATIONSHIP ===');
        
        // Get all pegawai users
        $pegawaiUsers = \App\Models\User::where('role', 'pegawai')->get();
        
        $this->info("\nProcessing " . $pegawaiUsers->count() . " pegawai users...\n");
        
        foreach ($pegawaiUsers as $user) {
            $this->line("Processing User ID {$user->id} ({$user->email})...");
            
            // Check if pegawai already exists for this user
            $pegawai = \App\Models\Pegawai::withoutGlobalScopes()->where('user_id', $user->id)->first();
            
            if ($pegawai) {
                $this->line("  ✅ Pegawai already exists: {$pegawai->nama}");
                
                // Update perusahaan_id if missing
                if (!$pegawai->perusahaan_id && $user->perusahaan_id) {
                    $pegawai->update(['perusahaan_id' => $user->perusahaan_id]);
                    $this->line("  ✅ Updated perusahaan_id to {$user->perusahaan_id}");
                }
            } else {
                // Create new pegawai record
                $this->line("  ⚠️  No pegawai found, creating new record...");
                
                try {
                    $pegawai = \App\Models\Pegawai::create([
                        'user_id' => $user->id,
                        'nama' => $user->name,
                        'email' => $user->email,
                        'perusahaan_id' => $user->perusahaan_id,
                        'kode_pegawai' => 'PGW' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                        'jenis_pegawai' => 'tetap',
                    ]);
                    
                    $this->line("  ✅ Created pegawai: {$pegawai->nama} (ID: {$pegawai->id})");
                } catch (\Exception $e) {
                    $this->line("  ❌ Error creating pegawai: " . $e->getMessage());
                }
            }
        }
        
        $this->info("\n=== FIX COMPLETE ===");
    }
}

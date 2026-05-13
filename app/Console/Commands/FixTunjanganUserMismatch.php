<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\Jabatan;
use App\Models\User;

class FixTunjanganUserMismatch extends Command
{
    protected $signature = 'fix:tunjangan-user-mismatch {--from-user=2 : Source user ID} {--to-user=1 : Target user ID} {--force : Force delete duplicates}';
    protected $description = 'Fix tunjangan issue by reassigning pegawai and jabatan to correct user';

    public function handle()
    {
        $fromUserId = $this->option('from-user');
        $toUserId = $this->option('to-user');
        $force = $this->option('force');

        $this->info("=== FIX TUNJANGAN USER MISMATCH ===");
        $this->newLine();

        // Check source user
        $fromUser = User::find($fromUserId);
        if (!$fromUser) {
            $this->error("Source User ID {$fromUserId} not found!");
            return 1;
        }

        // Check target user
        $toUser = User::find($toUserId);
        if (!$toUser) {
            $this->error("Target User ID {$toUserId} not found!");
            return 1;
        }

        $this->line("Source User: {$fromUser->name} (ID: {$fromUserId})");
        $this->line("Target User: {$toUser->name} (ID: {$toUserId})");
        $this->newLine();

        // Step 1: Check for duplicates in target user
        if ($force) {
            $this->info("Step 1: Removing duplicate data from target user (--force enabled)");
            
            $deletedJabatan = Jabatan::withoutGlobalScopes()
                ->where('user_id', $toUserId)
                ->delete();
            
            $deletedPegawai = Pegawai::withoutGlobalScopes()
                ->where('user_id', $toUserId)
                ->delete();
            
            $this->line("✓ Deleted {$deletedJabatan} jabatan and {$deletedPegawai} pegawai from target user");
            $this->newLine();
        }

        // Step 2: Get source data
        $this->info("Step 2: Retrieving data from source user");
        
        $sourceJabatans = Jabatan::withoutGlobalScopes()
            ->where('user_id', $fromUserId)
            ->get();

        $sourcePegawais = Pegawai::withoutGlobalScopes()
            ->where('user_id', $fromUserId)
            ->get();

        if ($sourceJabatans->isEmpty() && $sourcePegawais->isEmpty()) {
            $this->warn("No data found for source user!");
            return 1;
        }

        $this->line("✓ Found {$sourceJabatans->count()} jabatan and {$sourcePegawais->count()} pegawai");
        $this->newLine();

        // Step 3: Update jabatan
        $this->info("Step 3: Updating jabatan to target user");
        
        $jabatanCount = Jabatan::withoutGlobalScopes()
            ->where('user_id', $fromUserId)
            ->update(['user_id' => $toUserId]);

        $this->line("✓ Updated {$jabatanCount} jabatan");
        
        foreach ($sourceJabatans as $jabatan) {
            $this->line("  ├─ {$jabatan->nama}");
            $this->line("  │  ├─ Tunjangan Transport: " . number_format($jabatan->tunjangan_transport, 0, ',', '.'));
            $this->line("  │  └─ Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi, 0, ',', '.'));
        }
        $this->newLine();

        // Step 4: Update pegawai
        $this->info("Step 4: Updating pegawai to target user");
        
        $pegawaiCount = Pegawai::withoutGlobalScopes()
            ->where('user_id', $fromUserId)
            ->update(['user_id' => $toUserId]);

        $this->line("✓ Updated {$pegawaiCount} pegawai");
        
        foreach ($sourcePegawais as $pegawai) {
            $this->line("  ├─ {$pegawai->nama}");
            if ($pegawai->jabatan_id) {
                $jabatan = Jabatan::withoutGlobalScopes()->find($pegawai->jabatan_id);
                if ($jabatan) {
                    $this->line("  │  └─ Jabatan: {$jabatan->nama}");
                }
            }
        }
        $this->newLine();

        // Step 5: Verify
        $this->info("Step 5: Verification");
        
        $verifyJabatans = Jabatan::withoutGlobalScopes()
            ->where('user_id', $toUserId)
            ->count();
        
        $verifyPegawais = Pegawai::withoutGlobalScopes()
            ->where('user_id', $toUserId)
            ->count();

        $this->line("✓ Target user now has {$verifyJabatans} jabatan and {$verifyPegawais} pegawai");
        $this->newLine();

        $this->info("=== FIX COMPLETE ===");
        $this->info("✓ All data from User {$fromUserId} is now assigned to User {$toUserId}");
        $this->newLine();
        
        $this->line("Next steps:");
        $this->line("1. Refresh the browser page");
        $this->line("2. Go to Tambah Penggajian");
        $this->line("3. Select pegawai and verify tunjangan values appear");
        $this->newLine();
        
        $this->line("If still not showing:");
        $this->line("- Clear browser cache (Ctrl+Shift+Delete)");
        $this->line("- Run: php artisan cache:clear");
        $this->line("- Run: php artisan view:clear");

        return 0;
    }
}

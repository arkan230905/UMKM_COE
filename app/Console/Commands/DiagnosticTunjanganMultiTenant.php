<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Perusahaan;
use App\Models\Pegawai;
use App\Models\Jabatan;

class DiagnosticTunjanganMultiTenant extends Command
{
    protected $signature = 'diagnostic:tunjangan-multi-tenant {--user-id=}';
    protected $description = 'Diagnose tunjangan issue in multi-tenant environment';

    public function handle()
    {
        $this->info('=== DIAGNOSTIC: TUNJANGAN MULTI-TENANT ISSUE ===');
        $this->newLine();

        // STEP 1: Check all users and their perusahaan
        $this->info('STEP 1: Check all users and their perusahaan');
        $this->line('─────────────────────────────────────────');
        
        $users = User::with('perusahaan')->get(['id', 'name', 'email', 'perusahaan_id']);
        
        if ($users->isEmpty()) {
            $this->warn('No users found in database!');
        } else {
            foreach ($users as $user) {
                $this->line("User ID: {$user->id} | Name: {$user->name} | Email: {$user->email}");
                $this->line("  └─ Perusahaan ID: {$user->perusahaan_id} | Perusahaan: {$user->perusahaan?->nama ?? 'N/A'}");
            }
        }
        $this->newLine();

        // STEP 2: Check all perusahaan
        $this->info('STEP 2: Check all perusahaan');
        $this->line('─────────────────────────────────────────');
        
        $perusahaans = Perusahaan::all(['id', 'user_id', 'nama', 'kode']);
        
        if ($perusahaans->isEmpty()) {
            $this->warn('No perusahaan found in database!');
        } else {
            foreach ($perusahaans as $perusahaan) {
                $this->line("Perusahaan ID: {$perusahaan->id} | Nama: {$perusahaan->nama} | Kode: {$perusahaan->kode}");
                $this->line("  └─ User ID: {$perusahaan->user_id}");
            }
        }
        $this->newLine();

        // STEP 3: Check pegawai per user (without global scope)
        $this->info('STEP 3: Check pegawai per user (bypassing global scope)');
        $this->line('─────────────────────────────────────────');
        
        $pegawais = Pegawai::withoutGlobalScopes()->with('jabatanRelasi', 'perusahaan')->get(['id', 'user_id', 'perusahaan_id', 'nama', 'jabatan_id']);
        
        if ($pegawais->isEmpty()) {
            $this->warn('No pegawai found in database!');
        } else {
            foreach ($pegawais as $pegawai) {
                $this->line("Pegawai ID: {$pegawai->id} | Nama: {$pegawai->nama}");
                $this->line("  ├─ User ID: {$pegawai->user_id}");
                $this->line("  ├─ Perusahaan ID: {$pegawai->perusahaan_id} | Perusahaan: {$pegawai->perusahaan?->nama ?? 'N/A'}");
                $this->line("  ├─ Jabatan ID: {$pegawai->jabatan_id}");
                
                if ($pegawai->jabatanRelasi) {
                    $this->line("  ├─ Jabatan: {$pegawai->jabatanRelasi->nama}");
                    $this->line("  │  ├─ Gaji Pokok: " . number_format($pegawai->jabatanRelasi->gaji_pokok ?? 0, 0, ',', '.'));
                    $this->line("  │  ├─ Tunjangan Jabatan: " . number_format($pegawai->jabatanRelasi->tunjangan ?? 0, 0, ',', '.'));
                    $this->line("  │  ├─ Tunjangan Transport: " . number_format($pegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.'));
                    $this->line("  │  └─ Tunjangan Konsumsi: " . number_format($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0, 0, ',', '.'));
                } else {
                    $this->warn("  └─ Jabatan NOT FOUND (jabatan_id: {$pegawai->jabatan_id})");
                }
            }
        }
        $this->newLine();

        // STEP 4: Check jabatan per user (without global scope)
        $this->info('STEP 4: Check jabatan per user (bypassing global scope)');
        $this->line('─────────────────────────────────────────');
        
        $jabatans = Jabatan::withoutGlobalScopes()->get(['id', 'user_id', 'nama', 'gaji_pokok', 'tunjangan', 'tunjangan_transport', 'tunjangan_konsumsi']);
        
        if ($jabatans->isEmpty()) {
            $this->warn('No jabatan found in database!');
        } else {
            foreach ($jabatans as $jabatan) {
                $this->line("Jabatan ID: {$jabatan->id} | Nama: {$jabatan->nama}");
                $this->line("  ├─ User ID: {$jabatan->user_id}");
                $this->line("  ├─ Gaji Pokok: " . number_format($jabatan->gaji_pokok ?? 0, 0, ',', '.'));
                $this->line("  ├─ Tunjangan Jabatan: " . number_format($jabatan->tunjangan ?? 0, 0, ',', '.'));
                $this->line("  ├─ Tunjangan Transport: " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.'));
                $this->line("  └─ Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.'));
            }
        }
        $this->newLine();

        // STEP 5: Check if specific user has data
        if ($this->option('user-id')) {
            $userId = $this->option('user-id');
            $this->info("STEP 5: Check data for User ID: {$userId}");
            $this->line('─────────────────────────────────────────');
            
            $user = User::find($userId);
            if (!$user) {
                $this->error("User ID {$userId} not found!");
            } else {
                $this->line("User: {$user->name} ({$user->email})");
                $this->line("Perusahaan ID: {$user->perusahaan_id}");
                
                // Check pegawai for this user
                $pegawaisForUser = Pegawai::withoutGlobalScopes()
                    ->where('user_id', $userId)
                    ->with('jabatanRelasi')
                    ->get(['id', 'nama', 'jabatan_id']);
                
                $this->line("Pegawai count: {$pegawaisForUser->count()}");
                foreach ($pegawaisForUser as $pegawai) {
                    $this->line("  ├─ {$pegawai->nama} (ID: {$pegawai->id}, Jabatan ID: {$pegawai->jabatan_id})");
                    if ($pegawai->jabatanRelasi) {
                        $this->line("  │  └─ Tunjangan Transport: " . number_format($pegawai->jabatanRelasi->tunjangan_transport ?? 0, 0, ',', '.'));
                    }
                }
                
                // Check jabatan for this user
                $jabatansForUser = Jabatan::withoutGlobalScopes()
                    ->where('user_id', $userId)
                    ->get(['id', 'nama', 'tunjangan_transport', 'tunjangan_konsumsi']);
                
                $this->line("Jabatan count: {$jabatansForUser->count()}");
                foreach ($jabatansForUser as $jabatan) {
                    $this->line("  ├─ {$jabatan->nama} (ID: {$jabatan->id})");
                    $this->line("  │  ├─ Tunjangan Transport: " . number_format($jabatan->tunjangan_transport ?? 0, 0, ',', '.'));
                    $this->line("  │  └─ Tunjangan Konsumsi: " . number_format($jabatan->tunjangan_konsumsi ?? 0, 0, ',', '.'));
                }
            }
            $this->newLine();
        }

        // STEP 6: Summary
        $this->info('STEP 6: Summary');
        $this->line('─────────────────────────────────────────');
        
        $totalUsers = User::count();
        $totalPerusahaan = Perusahaan::count();
        $totalPegawai = Pegawai::withoutGlobalScopes()->count();
        $totalJabatan = Jabatan::withoutGlobalScopes()->count();
        
        $this->line("Total Users: {$totalUsers}");
        $this->line("Total Perusahaan: {$totalPerusahaan}");
        $this->line("Total Pegawai: {$totalPegawai}");
        $this->line("Total Jabatan: {$totalJabatan}");
        
        if ($totalUsers === 0 || $totalPegawai === 0 || $totalJabatan === 0) {
            $this->warn('⚠️  ISSUE DETECTED: Missing data in production!');
            $this->line('Possible causes:');
            $this->line('1. Database not seeded with initial data');
            $this->line('2. Data belongs to different user/tenant');
            $this->line('3. Global scope filtering is hiding data');
        } else {
            $this->info('✓ Data exists in database');
        }
        
        $this->newLine();
        $this->info('=== END DIAGNOSTIC ===');
    }
}

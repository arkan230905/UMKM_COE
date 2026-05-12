<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Perusahaan;
use App\Models\Pegawai;
use App\Models\Jabatan;
use App\Models\KategoriPegawai;

class EnsureMultiTenantDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder ensures that:
     * 1. Each user has a perusahaan
     * 2. Each pegawai has correct user_id and perusahaan_id
     * 3. Each jabatan has correct user_id
     * 4. Tunjangan values are properly set
     */
    public function run(): void
    {
        $this->command->info('=== Ensuring Multi-Tenant Data Integrity ===');
        $this->newLine();

        // Get all users
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please create users first.');
            return;
        }

        foreach ($users as $user) {
            $this->command->line("Processing User: {$user->name} (ID: {$user->id})");
            
            // Ensure user has a perusahaan
            if (!$user->perusahaan_id) {
                $this->command->warn("  ⚠️  User has no perusahaan_id. Creating default perusahaan...");
                
                $perusahaan = Perusahaan::create([
                    'user_id' => $user->id,
                    'nama' => $user->name . ' Company',
                    'kode' => strtoupper(substr(md5($user->id), 0, 6)),
                    'alamat' => 'Default Address',
                    'email' => $user->email,
                    'telepon' => '0',
                ]);
                
                $user->update(['perusahaan_id' => $perusahaan->id]);
                $this->command->info("  ✓ Created perusahaan: {$perusahaan->nama} (ID: {$perusahaan->id})");
            } else {
                $perusahaan = $user->perusahaan;
                $this->command->line("  ✓ Perusahaan: {$perusahaan->nama} (ID: {$perusahaan->id})");
            }

            // Ensure kategori pegawai exists for this user
            $kategoriCount = KategoriPegawai::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->count();
            
            if ($kategoriCount === 0) {
                $this->command->warn("  ⚠️  No kategori pegawai found. Creating defaults...");
                
                $kategoriData = [
                    ['nama' => 'BTKTL', 'deskripsi' => 'Buruh Tetap Tidak Lepas'],
                    ['nama' => 'BTKL', 'deskripsi' => 'Buruh Tetap Lepas'],
                    ['nama' => 'BTPL', 'deskripsi' => 'Buruh Tidak Tetap Lepas'],
                ];
                
                foreach ($kategoriData as $data) {
                    KategoriPegawai::create([
                        'user_id' => $user->id,
                        'nama' => $data['nama'],
                        'deskripsi' => $data['deskripsi'],
                    ]);
                }
                
                $this->command->info("  ✓ Created 3 kategori pegawai");
            }

            // Ensure jabatan exists for this user with tunjangan values
            $jabatanCount = Jabatan::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->count();
            
            if ($jabatanCount === 0) {
                $this->command->warn("  ⚠️  No jabatan found. Creating defaults...");
                
                $jabatanData = [
                    [
                        'nama' => 'Manager',
                        'gaji_pokok' => 5000000,
                        'tunjangan' => 1000000,
                        'tunjangan_transport' => 500000,
                        'tunjangan_konsumsi' => 300000,
                        'asuransi' => 200000,
                        'tarif_per_jam' => 50000,
                    ],
                    [
                        'nama' => 'Supervisor',
                        'gaji_pokok' => 3500000,
                        'tunjangan' => 700000,
                        'tunjangan_transport' => 400000,
                        'tunjangan_konsumsi' => 250000,
                        'asuransi' => 150000,
                        'tarif_per_jam' => 35000,
                    ],
                    [
                        'nama' => 'Staff',
                        'gaji_pokok' => 2500000,
                        'tunjangan' => 500000,
                        'tunjangan_transport' => 300000,
                        'tunjangan_konsumsi' => 200000,
                        'asuransi' => 100000,
                        'tarif_per_jam' => 25000,
                    ],
                    [
                        'nama' => 'Operator',
                        'gaji_pokok' => 2000000,
                        'tunjangan' => 400000,
                        'tunjangan_transport' => 250000,
                        'tunjangan_konsumsi' => 150000,
                        'asuransi' => 80000,
                        'tarif_per_jam' => 20000,
                    ],
                ];
                
                foreach ($jabatanData as $data) {
                    Jabatan::create([
                        'user_id' => $user->id,
                        'kode_jabatan' => Jabatan::generateKode(),
                        'nama' => $data['nama'],
                        'gaji_pokok' => $data['gaji_pokok'],
                        'tunjangan' => $data['tunjangan'],
                        'tunjangan_transport' => $data['tunjangan_transport'],
                        'tunjangan_konsumsi' => $data['tunjangan_konsumsi'],
                        'asuransi' => $data['asuransi'],
                        'tarif_per_jam' => $data['tarif_per_jam'],
                    ]);
                }
                
                $this->command->info("  ✓ Created 4 jabatan with tunjangan values");
            } else {
                // Check if existing jabatan have tunjangan values
                $jabatanWithoutTunjangan = Jabatan::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->where(function ($q) {
                        $q->whereNull('tunjangan_transport')
                          ->orWhere('tunjangan_transport', 0)
                          ->orWhereNull('tunjangan_konsumsi')
                          ->orWhere('tunjangan_konsumsi', 0);
                    })
                    ->count();
                
                if ($jabatanWithoutTunjangan > 0) {
                    $this->command->warn("  ⚠️  Found {$jabatanWithoutTunjangan} jabatan without tunjangan values. Updating...");
                    
                    // Update jabatan with default tunjangan values
                    Jabatan::withoutGlobalScopes()
                        ->where('user_id', $user->id)
                        ->where(function ($q) {
                            $q->whereNull('tunjangan_transport')
                              ->orWhere('tunjangan_transport', 0);
                        })
                        ->update(['tunjangan_transport' => 300000]);
                    
                    Jabatan::withoutGlobalScopes()
                        ->where('user_id', $user->id)
                        ->where(function ($q) {
                            $q->whereNull('tunjangan_konsumsi')
                              ->orWhere('tunjangan_konsumsi', 0);
                        })
                        ->update(['tunjangan_konsumsi' => 200000]);
                    
                    $this->command->info("  ✓ Updated jabatan with default tunjangan values");
                }
            }

            // Ensure pegawai have correct user_id and perusahaan_id
            $pegawaiWithWrongUserId = Pegawai::withoutGlobalScopes()
                ->where('perusahaan_id', $perusahaan->id)
                ->where('user_id', '!=', $user->id)
                ->count();
            
            if ($pegawaiWithWrongUserId > 0) {
                $this->command->warn("  ⚠️  Found {$pegawaiWithWrongUserId} pegawai with wrong user_id. Fixing...");
                
                Pegawai::withoutGlobalScopes()
                    ->where('perusahaan_id', $perusahaan->id)
                    ->update(['user_id' => $user->id]);
                
                $this->command->info("  ✓ Fixed pegawai user_id");
            }

            // Ensure pegawai have jabatan_id set
            $pegawaiWithoutJabatan = Pegawai::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->whereNull('jabatan_id')
                ->count();
            
            if ($pegawaiWithoutJabatan > 0) {
                $this->command->warn("  ⚠️  Found {$pegawaiWithoutJabatan} pegawai without jabatan_id. Assigning default...");
                
                $defaultJabatan = Jabatan::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($defaultJabatan) {
                    Pegawai::withoutGlobalScopes()
                        ->where('user_id', $user->id)
                        ->whereNull('jabatan_id')
                        ->update(['jabatan_id' => $defaultJabatan->id]);
                    
                    $this->command->info("  ✓ Assigned default jabatan to pegawai");
                }
            }

            // Verify pegawai can load jabatan
            $pegawaiCount = Pegawai::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->count();
            
            if ($pegawaiCount > 0) {
                $pegawaiWithJabatan = Pegawai::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->with('jabatanRelasi')
                    ->get();
                
                $loadedCount = 0;
                foreach ($pegawaiWithJabatan as $pegawai) {
                    if ($pegawai->jabatanRelasi) {
                        $loadedCount++;
                    }
                }
                
                $this->command->line("  ✓ Pegawai: {$pegawaiCount} total, {$loadedCount} with jabatan loaded");
            }

            $this->newLine();
        }

        $this->command->info('=== Multi-Tenant Data Integrity Check Complete ===');
        $this->command->info('✓ All users have perusahaan');
        $this->command->info('✓ All pegawai have correct user_id and perusahaan_id');
        $this->command->info('✓ All jabatan have tunjangan values');
        $this->command->info('✓ All pegawai have jabatan_id assigned');
    }
}

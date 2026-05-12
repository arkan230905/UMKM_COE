<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pegawai;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateUsersFromPegawaiSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan perusahaan demo sudah ada
        $perusahaan = Perusahaan::where('kode', 'DEMO-001')->first();
        if (!$perusahaan) {
            $perusahaan = Perusahaan::create([
                'nama' => 'UMKM Demo Company',
                'alamat' => 'Jl. Demo No. 123, Jakarta Pusat',
                'email' => 'demo@umkm.com',
                'telepon' => '021-1234567',
                'kode' => 'DEMO-001',
            ]);
        }

        // Ambil semua pegawai yang ada
        $pegawais = Pegawai::all();
        
        if ($pegawais->isEmpty()) {
            $this->command->info('Tidak ada data pegawai. Pastikan tabel pegawais sudah memiliki data.');
            return;
        }

        foreach ($pegawais as $pegawai) {
            // Tentukan role berdasarkan jabatan
            $role = $this->determineRole($pegawai->jabatan);
            
            // Buat user account untuk pegawai
            $user = User::firstOrCreate(
                ['email' => $pegawai->email],
                [
                    'name' => $pegawai->nama,
                    'password' => Hash::make('password123'), // Default password
                    'role' => $role,
                    'pegawai_id' => $pegawai->id, // CRITICAL: Link user ke pegawai
                    'perusahaan_id' => $perusahaan->id, // CRITICAL: Link user ke perusahaan
                    'email_verified_at' => now(),
                ]
            );
            
            // Jika user sudah ada tapi pegawai_id kosong, update
            if (empty($user->pegawai_id)) {
                $user->update(['pegawai_id' => $pegawai->id]);
            }
            
            // Jika user sudah ada tapi perusahaan_id kosong, update
            if (empty($user->perusahaan_id)) {
                $user->update(['perusahaan_id' => $perusahaan->id]);
            }
            
            // Update pegawai dengan user_id dan perusahaan_id jika belum ada
            if (empty($pegawai->user_id) || empty($pegawai->perusahaan_id)) {
                $pegawai->update([
                    'user_id' => $user->id,
                    'perusahaan_id' => $perusahaan->id
                ]);
            }
            
            $this->command->info("User created for: {$pegawai->nama} ({$pegawai->email}) - Role: {$role} - Pegawai ID: {$pegawai->id} - Perusahaan ID: {$perusahaan->id}");
        }
        
        $this->command->info('User accounts created for all existing employees!');
    }
    
    private function determineRole(string $jabatan): string
    {
        $jabatan = strtolower($jabatan);
        
        if (str_contains($jabatan, 'kasir')) {
            return 'kasir';
        }
        
        if (str_contains($jabatan, 'gudang')) {
            return 'gudang';
        }
        
        if (str_contains($jabatan, 'admin')) {
            return 'admin';
        }
        
        if (str_contains($jabatan, 'supervisor') || str_contains($jabatan, 'manager')) {
            return 'supervisor';
        }
        
        // Default role for other positions
        return 'pegawai';
    }
}
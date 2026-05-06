<?php

namespace App\Observers;

use App\Models\User;
use App\Services\InitMasterDataService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        try {
            // Hanya initialize kategori untuk user biasa (bukan admin)
            if ($user->role !== 'admin') {
                Log::info("New user created: {$user->id} ({$user->email}), initializing kategori pegawai");
                
                // Buat kategori BTKL dan BTKTL untuk user baru
                $this->createKategoriPegawai($user->id);
                
                Log::info("Kategori pegawai initialized for user {$user->id}");
            } else {
                Log::info("Admin user created: {$user->id} ({$user->email}), skipping kategori initialization");
            }
        } catch (\Exception $e) {
            Log::error("Failed to initialize kategori for user {$user->id}: " . $e->getMessage());
            // Don't throw exception to prevent user creation failure
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Optional: Handle role changes
        if ($user->isDirty('role') && $user->role !== 'admin') {
            try {
                Log::info("User role changed to non-admin: {$user->id}, checking master data");
                
                $initService = new InitMasterDataService();
                $initService->initializeForUser($user->id);
            } catch (\Exception $e) {
                Log::error("Failed to initialize master data after role change for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Master data akan terhapus otomatis karena foreign key cascade
        Log::info("User deleted: {$user->id}, master data will be cleaned up automatically");
    }
    
    /**
     * Create default kategori pegawai for new user
     */
    private function createKategoriPegawai($userId)
    {
        $kategoriData = [
            [
                'nama' => 'BTKL',
                'deskripsi' => 'Tenaga Kerja Langsung'
            ],
            [
                'nama' => 'BTKTL',
                'deskripsi' => 'Bukan Tenaga Kerja Langsung'
            ]
        ];
        
        foreach ($kategoriData as $kategori) {
            // Cek apakah kategori sudah ada untuk user ini
            $exists = DB::table('kategori_pegawai')
                ->where('user_id', $userId)
                ->where('nama', $kategori['nama'])
                ->exists();
                
            if (!$exists) {
                // Insert kategori pegawai
                DB::table('kategori_pegawai')->insert([
                    'user_id' => $userId,
                    'nama' => $kategori['nama'],
                    'deskripsi' => $kategori['deskripsi'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
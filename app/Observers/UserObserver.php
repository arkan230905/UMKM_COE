<?php

namespace App\Observers;

use App\Models\User;
use App\Services\InitMasterDataService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        try {
            // Hanya initialize master data untuk user biasa (bukan admin)
            if ($user->role !== 'admin') {
                Log::info("New user created: {$user->id} ({$user->email}), initializing master data");
                
                $initService = new InitMasterDataService();
                $result = $initService->initializeForUser($user->id);
                
                if ($result && $result['success']) {
                    Log::info("Master data initialized for user {$user->id}: " . 
                             "{$result['bahan_baku_count']} bahan baku, " . 
                             "{$result['bahan_pendukung_count']} bahan pendukung");
                }
            } else {
                Log::info("Admin user created: {$user->id} ({$user->email}), skipping master data initialization");
            }
        } catch (\Exception $e) {
            Log::error("Failed to initialize master data for user {$user->id}: " . $e->getMessage());
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
}
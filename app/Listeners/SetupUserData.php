<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Support\Facades\Log;

class SetupUserData
{
    /**
     * Handle the event.
     * Setup untuk owner baru yang registrasi
     * 
     * CATATAN: Sistem ini menggunakan DATA GLOBAL yang di-share oleh semua perusahaan.
     * Owner baru tidak perlu di-seed karena sudah bisa akses data master yang ada.
     * 
     * Data master yang sudah terkunci (global):
     * - COA (Chart of Accounts): 405 records
     * - Satuan: 15 records
     * - Jabatan: 2 records
     * - Pegawai: 2 records
     * - Produk: 1 record
     * - Bahan Baku: 2 records
     * - Bahan Pendukung: 4 records
     * - Dan lainnya...
     * 
     * Total: 434 records master data yang terkunci
     */
    public function handle(UserRegistered $event): void
    {
        try {
            if ($event->user->role === 'owner' && $event->companyId) {
                Log::info('New owner registered - using global master data', [
                    'user_id' => $event->user->id,
                    'company_id' => $event->companyId,
                    'user_name' => $event->user->name,
                    'company_name' => $event->user->perusahaan->nama ?? 'Unknown',
                ]);
                
                // Data master sudah global dan terkunci
                // Owner baru langsung bisa akses semua data master yang ada
                // Tidak perlu seed per owner
                
                Log::info('Owner setup completed - ready to use global master data', [
                    'user_id' => $event->user->id,
                    'company_id' => $event->companyId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to setup owner', [
                'user_id' => $event->user->id,
                'company_id' => $event->companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Don't throw exception, just log it
            // Owner masih bisa login dan akses data global
        }
    }
}

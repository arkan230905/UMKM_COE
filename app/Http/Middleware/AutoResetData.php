<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AutoResetData
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek jika user sudah login
        if (Auth::check()) {
            $currentUser = Auth::user();
            $sessionKey = 'last_user_id_' . $currentUser->id;
            
            // Cek apakah user berbeda dengan session sebelumnya
            $lastUserId = Session::get($sessionKey);
            
            if ($lastUserId !== null && $lastUserId != $currentUser->id) {
                // User berbeda, reset data
                $this->resetAllData($currentUser, $lastUserId);
            }
            
            // Simpan user ID ke session
            Session::put($sessionKey, $currentUser->id);
        }
        
        return $next($request);
    }
    
    /**
     * Reset semua data untuk user berbeda
     */
    private function resetAllData($currentUser, $lastUserId)
    {
        try {
            // Log perpindahan user
            \Log::info("Auto Reset Data: User berbeda terdeteksi", [
                'current_user_id' => $currentUser->id,
                'current_user_email' => $currentUser->email,
                'last_user_id' => $lastUserId,
                'timestamp' => now()->toISOString()
            ]);
            
            // Backup data sebelum reset
            $this->backupDataBeforeReset();
            
            // Reset semua tabel
            $this->resetAllTables();
            
            // Log berhasil reset
            \Log::info("Auto Reset Data: Semua data berhasil direset", [
                'current_user_id' => $currentUser->id,
                'current_user_email' => $currentUser->email,
                'reset_time' => now()->toISOString()
            ]);
            
            // Flash message untuk user
            Session::flash('success', 'Selamat datang! Semua data telah direset untuk perusahaan baru.');
            
        } catch (\Exception $e) {
            \Log::error("Auto Reset Data: Gagal reset data", [
                'error' => $e->getMessage(),
                'current_user_id' => $currentUser->id,
                'timestamp' => now()->toISOString()
            ]);
            
            Session::flash('error', 'Terjadi kesalahan saat reset data. Silakan hubungi administrator.');
        }
    }
    
    /**
     * Backup data sebelum reset
     */
    private function backupDataBeforeReset()
    {
        $tablesToBackup = [
            'proses_produksis', 'bop_proses', 'btkls', 'bom_job_btkl',
            'proses_bops', 'bom_proses', 'bom_proses_bops', 'produksis',
            'produksi_details', 'pembelians', 'pembelian_details', 'penjualans',
            'detail_penjualans', 'retur_penjualans', 'detail_retur_penjualans',
            'stocks', 'stock_mutations', 'bahan_bakus', 'bahan_pendukungs',
            'pegawais', 'jabatans', 'asets', 'coas'
        ];
        
        $backupData = [];
        foreach ($tablesToBackup as $table) {
            if (Schema::hasTable($table)) {
                $data = DB::table($table)->get();
                if ($data->count() > 0) {
                    $backupData[$table] = $data->toArray();
                }
            }
        }
        
        if (!empty($backupData)) {
            $backupFile = 'auto_reset_backup_' . date('Y-m-d_H-i-s') . '.json';
            file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
            
            \Log::info("Auto Reset Data: Backup berhasil", [
                'backup_file' => $backupFile,
                'total_records' => array_sum(array_map('count', $backupData))
            ]);
        }
    }
    
    /**
     * Reset semua tabel
     */
    private function resetAllTables()
    {
        $tablesToReset = [
            'proses_produksis', 'bop_proses', 'btkls', 'bom_job_btkl',
            'proses_bops', 'bom_proses', 'bom_proses_bops', 'produksis',
            'produksi_details', 'pembelians', 'pembelian_details', 'penjualans',
            'detail_penjualans', 'retur_penjualans', 'detail_retur_penjualans',
            'stocks', 'stock_mutations', 'bahan_bakus', 'bahan_pendukungs',
            'pegawais', 'jabatans', 'asets', 'coas'
        ];
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        foreach ($tablesToReset as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                if ($count > 0) {
                    DB::table($table)->truncate();
                    
                    // Reset auto increment
                    try {
                        DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                    } catch (\Exception $e) {
                        // Ignore error jika tabel tidak support auto increment
                    }
                }
            }
        }
        
        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

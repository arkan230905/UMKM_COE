<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AutoResetController extends Controller
{
    /**
     * Handle auto reset request
     */
    public function checkAndReset(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        $currentUser = Auth::user();
        $sessionKey = 'auto_reset_user_' . $currentUser->id;
        
        // Cek apakah ini adalah login pertama atau user berbeda
        $lastResetUser = Session::get($sessionKey);
        
        if ($lastResetUser === null) {
            // Login pertama kali ini, simpan user ID
            Session::put($sessionKey, $currentUser->id);
            
            return response()->json([
                'success' => true,
                'message' => 'First login detected',
                'action' => 'saved_session'
            ]);
        }
        
        if ($lastResetUser != $currentUser->id) {
            // User berbeda, lakukan reset
            $this->performAutoReset($currentUser, $lastResetUser);
            
            // Update session dengan user baru
            Session::put($sessionKey, $currentUser->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Auto reset completed for different user',
                'action' => 'data_reset',
                'previous_user_id' => $lastResetUser,
                'current_user_id' => $currentUser->id
            ]);
        }
        
        // User sama, tidak perlu reset
        return response()->json([
            'success' => true,
            'message' => 'Same user, no reset needed',
            'action' => 'no_action'
        ]);
    }
    
    /**
     * Perform auto reset
     */
    private function performAutoReset($currentUser, $lastUserId)
    {
        try {
            Log::info("Auto Reset: Starting reset for user change", [
                'previous_user_id' => $lastUserId,
                'current_user_id' => $currentUser->id,
                'current_email' => $currentUser->email,
                'timestamp' => now()->toISOString()
            ]);
            
            // Reset data menggunakan script yang sudah ada
            $this->resetDatabaseTables();
            
            Log::info("Auto Reset: Reset completed successfully", [
                'current_user_id' => $currentUser->id,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Auto Reset: Failed to reset data", [
                'error' => $e->getMessage(),
                'current_user_id' => $currentUser->id,
                'timestamp' => now()->toISOString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Reset database tables
     */
    private function resetDatabaseTables()
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
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        foreach ($tablesToReset as $table) {
            if (\Schema::hasTable($table)) {
                \DB::table($table)->truncate();
                
                // Reset auto increment
                try {
                    \DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                } catch (\Exception $e) {
                    // Ignore error untuk tabel yang tidak support auto increment
                }
            }
        }
        
        // Enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
    
    /**
     * Get reset history
     */
    public function getResetHistory()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }
        
        // Ambil log reset terakhir
        $resetLogs = Log::channel()
            ->read('database')
            ->where('message', 'like', '%Auto Reset:%')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $resetLogs
        ]);
    }
}

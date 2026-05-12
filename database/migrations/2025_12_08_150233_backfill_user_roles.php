<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find all users with null or empty role
        $usersWithoutRole = DB::table('users')
            ->whereNull('role')
            ->orWhere('role', '')
            ->get();

        foreach ($usersWithoutRole as $user) {
            // Determine appropriate role based on perusahaan_id
            $newRole = $user->perusahaan_id ? 'admin' : 'pelanggan';
            
            // Update the user's role
            DB::table('users')
                ->where('id', $user->id)
                ->update(['role' => $newRole]);
            
            // Log the change
            Log::info("Backfilled role for user ID {$user->id}: assigned role '{$newRole}'", [
                'user_id' => $user->id,
                'email' => $user->email,
                'assigned_role' => $newRole,
                'has_perusahaan' => (bool) $user->perusahaan_id,
            ]);
        }

        // Log summary
        $count = $usersWithoutRole->count();
        if ($count > 0) {
            Log::info("Backfilled roles for {$count} users");
        } else {
            Log::info("No users found without roles - migration skipped");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is idempotent and doesn't need to be reversed
        // We don't want to remove roles that were assigned
        Log::info("Backfill user roles migration rollback - no action taken");
    }
};

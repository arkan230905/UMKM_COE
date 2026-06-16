<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * CRITICAL FIX: Change kode_proses unique constraint from global to per-user
     * 
     * Before: unique constraint on kode_proses alone (prevents PRO-001 in multiple users)
     * After: unique constraint on (user_id, kode_proses) - allows each user to have PRO-001
     */
    public function up(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Drop the old global unique constraint
            $table->dropUnique('proses_produksis_kode_proses_unique');
            
            // Add new composite unique constraint on (user_id, kode_proses)
            $table->unique(['user_id', 'kode_proses'], 'proses_produksis_user_kode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Drop the new composite constraint
            $table->dropUnique('proses_produksis_user_kode_unique');
            
            // Restore the old global unique constraint
            $table->unique('kode_proses', 'proses_produksis_kode_proses_unique');
        });
    }
};

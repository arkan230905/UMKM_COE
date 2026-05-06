<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 🔒 SECURITY: Remove global unique constraint that prevents multi-tenant isolation
        // The jabatans_nama_unique constraint prevents different users from having 
        // jabatan with the same nama, which violates multi-tenant principles
        
        Schema::table('jabatans', function (Blueprint $table) {
            // Drop the problematic global unique constraint
            $table->dropUnique('jabatans_nama_unique');
            
            // Add composite unique constraint that includes user_id for proper multi-tenant isolation
            $table->unique(['nama', 'user_id'], 'jabatans_nama_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('jabatans_nama_user_unique');
            
            // Restore the old global unique constraint
            $table->unique('nama', 'jabatans_nama_unique');
        });
    }
};

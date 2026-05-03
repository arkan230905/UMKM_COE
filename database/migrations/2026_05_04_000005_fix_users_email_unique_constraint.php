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
        // 🔒 SECURITY: Remove global email unique constraint that prevents multi-tenant isolation
        // The users_email_unique constraint prevents different users from having 
        // pelanggan with the same email, which violates multi-tenant principles
        
        Schema::table('users', function (Blueprint $table) {
            // Drop problematic global unique constraint
            $table->dropUnique('users_email_unique');
            
            // Add composite unique constraint that includes user_id for proper multi-tenant isolation
            $table->unique(['email', 'user_id'], 'users_email_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('users_email_user_unique');
            
            // Restore the old global unique constraint
            $table->unique('email', 'users_email_unique');
        });
    }
};

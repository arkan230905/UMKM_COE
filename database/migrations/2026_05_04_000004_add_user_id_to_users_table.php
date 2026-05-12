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
        // 🔒 SECURITY: Add user_id column to users table for multi-tenant isolation
        // This is critical for proper multi-tenant architecture where pelanggan users
        // should be scoped to their owner (user_id)
        
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            
            // Add foreign key constraint if users table self-references
            // Note: This creates a hierarchical user structure where pelanggan users belong to owners
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add index for better performance
            $table->index('user_id');
        });
        
        // Update existing pelanggan users to have user_id = their own id (for migration purposes)
        // In a real multi-tenant system, pelanggan users should be created by their owners
        \DB::table('users')
            ->where('role', 'pelanggan')
            ->whereNull('user_id')
            ->update(['user_id' => \DB::raw('id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['user_id']);
            
            // Drop column
            $table->dropColumn('user_id');
        });
    }
};

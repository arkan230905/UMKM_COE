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
        Schema::table('pegawais', function (Blueprint $table) {
            // Add user_id column for multi-tenant isolation
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
            
            // Add foreign key constraint to users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add index for better performance
            $table->index('user_id');
        });
        
        // Update existing records to belong to the first user (ID=1) as default
        // This is a temporary fix - in production, you might want to handle this differently
        \DB::table('pegawais')->update(['user_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Drop foreign key and index first
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            
            // Drop the column
            $table->dropColumn('user_id');
        });
    }
};

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
        Schema::table('kategori_produks', function (Blueprint $table) {
            // Add user_id column for multi-tenant isolation
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add index for better query performance
            $table->index('user_id');
        });
        
        // Backfill existing data with user_id from related produks
        DB::statement("
            UPDATE kategori_produks kp
            LEFT JOIN produks p ON p.kategori_id = kp.id
            SET kp.user_id = p.user_id
            WHERE kp.user_id IS NULL AND p.user_id IS NOT NULL
            LIMIT 1
        ");
        
        // Set remaining NULL user_id to first user (fallback)
        DB::statement("
            UPDATE kategori_produks
            SET user_id = (SELECT id FROM users ORDER BY id ASC LIMIT 1)
            WHERE user_id IS NULL
        ");
        
        // Make user_id NOT NULL after backfill
        Schema::table('kategori_produks', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_produks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

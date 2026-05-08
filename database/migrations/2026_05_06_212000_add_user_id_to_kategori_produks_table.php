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
            // Check if user_id column doesn't exist before adding it
            if (!Schema::hasColumn('kategori_produks', 'user_id')) {
                // Add user_id column for multi-tenant isolation
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                
                // Add foreign key constraint
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                
                // Add index for better performance
                $table->index('user_id');
            }
        });
        
        // Backfill existing data with user_id from related produks
        DB::statement("
            UPDATE kategori_produks kp
            LEFT JOIN produks p ON p.kategori_id = kp.id
            SET kp.user_id = p.user_id
            WHERE kp.user_id IS NULL AND p.user_id IS NOT NULL
        ");
        
        // Set remaining NULL user_id to first user (fallback)
        DB::statement("
            UPDATE kategori_produks
            SET user_id = (SELECT id FROM users ORDER BY id ASC LIMIT 1)
            WHERE user_id IS NULL
        ");
        
        // Clean up any invalid user_id values before making column NOT NULL
        DB::statement("
            UPDATE kategori_produks
            SET user_id = NULL
            WHERE user_id = 0 OR user_id = ''
        ");
        
        // Skip making user_id NOT NULL to avoid data truncation issues
        // Schema::table('kategori_produks', function (Blueprint $table) {
        //     if (Schema::hasColumn('kategori_produks', 'user_id')) {
        //         $table->unsignedBigInteger('user_id')->nullable(false)->change();
        //     }
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_produks', function (Blueprint $table) {
            if (Schema::hasColumn('kategori_produks', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};

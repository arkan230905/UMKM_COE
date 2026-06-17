<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add user_id column to jenis_asets and kategori_asets tables
     * for multi-tenant support with global data capability.
     * 
     * - user_id = NULL: Global/shared data (accessible by all users)
     * - user_id = X: User-specific data (only accessible by that user)
     */
    public function up(): void
    {
        // Add user_id to jenis_asets
        if (!Schema::hasColumn('jenis_asets', 'user_id')) {
            Schema::table('jenis_asets', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable() // CRITICAL: Nullable for global data
                    ->after('id')
                    ->constrained('users')
                    ->onDelete('cascade');
                
                $table->index('user_id');
            });
        }

        // Add user_id to kategori_asets
        if (!Schema::hasColumn('kategori_asets', 'user_id')) {
            Schema::table('kategori_asets', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable() // CRITICAL: Nullable for global data
                    ->after('id')
                    ->constrained('users')
                    ->onDelete('cascade');
                
                $table->index('user_id');
            });
        }

        // Set existing data as global (user_id = NULL)
        // This preserves default data inserted during initial migration
        DB::table('jenis_asets')->whereNull('user_id')->update(['user_id' => null]);
        DB::table('kategori_asets')->whereNull('user_id')->update(['user_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('jenis_asets', 'user_id')) {
            Schema::table('jenis_asets', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('kategori_asets', 'user_id')) {
            Schema::table('kategori_asets', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};

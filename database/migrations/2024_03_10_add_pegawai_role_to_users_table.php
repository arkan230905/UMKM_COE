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
        Schema::table('users', function (Blueprint $table) {
            // Add pegawai_id column if it doesn't exist
            if (!Schema::hasColumn('users', 'pegawai_id')) {
                $table->unsignedBigInteger('pegawai_id')->nullable()->after('id');
            }
            
            // Update role column to include 'pegawai'
            if (Schema::hasColumn('users', 'role')) {
                // For existing role column, we'll update it in the seeder
                // The column should already be ENUM or VARCHAR that can store 'pegawai'
            } else {
                $table->enum('role', ['admin', 'owner', 'pegawai'])->default('pegawai')->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pegawai_id')) {
                $table->dropColumn('pegawai_id');
            }
            // Don't drop role column as it might be used by other parts
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Update role column ENUM to include all required roles
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop existing role column and recreate with all roles
            $table->dropColumn('role');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Recreate role column with all required roles
            $table->enum('role', [
                'admin',
                'owner', 
                'pegawai',
                'pelanggan',
                'pegawai_pembelian',
                'kasir'
            ])->default('pegawai')->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Recreate with original limited roles
            $table->enum('role', ['admin', 'owner', 'pegawai'])->default('pegawai')->after('phone');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing plain_password column to users table
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add plain_password column for admin view
            $table->string('plain_password')->nullable()->after('password')
                ->comment('Plain text password for admin view only');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('plain_password');
        });
    }
};

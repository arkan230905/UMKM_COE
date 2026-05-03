<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Drop the global unique constraint on email
            // Try both possible constraint names
            try {
                $table->dropUnique('pegawais_email_unique');
            } catch (\Exception $e) {
                // Constraint might have different name, try via raw SQL
                try {
                    DB::statement('ALTER TABLE pegawais DROP INDEX pegawais_email_unique');
                } catch (\Exception $e2) {
                    // Already dropped or doesn't exist, continue
                }
            }

            // Add composite unique: email + user_id (multi-tenant safe)
            // Only if user_id column exists
            if (Schema::hasColumn('pegawais', 'user_id')) {
                $table->unique(['email', 'user_id'], 'pegawais_email_user_id_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Restore global unique on email
            try {
                $table->dropUnique('pegawais_email_user_id_unique');
            } catch (\Exception $e) {
                // ignore
            }
            $table->unique('email');
        });
    }
};

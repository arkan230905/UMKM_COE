<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'username')) {
                    // SQLite tidak mendukung AFTER(); cukup tambahkan kolom di akhir.
                    $table->string('username');
                }
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone', 30)->nullable();
                }
            });

            // Note: Tidak membuat index unik di sini untuk kompatibilitas SQLite.
            // Validasi unik username ditangani di aplikasi.
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'username')) {
                    try { $table->dropUnique('users_username_unique'); } catch (\Throwable $e) {}
                    try { $table->dropColumn('username'); } catch (\Throwable $e) {}
                }
                if (Schema::hasColumn('users', 'phone')) {
                    try { $table->dropColumn('phone'); } catch (\Throwable $e) {}
                }
            });
        }
    }
};

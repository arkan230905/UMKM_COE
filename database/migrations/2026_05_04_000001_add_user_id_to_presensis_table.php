<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('presensis', 'user_id')) {
            Schema::table('presensis', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
            });
        }

        // Backfill user_id dari relasi pegawai -> user (skip jika kolom belum ada)
        if (Schema::hasColumn('pegawais', 'user_id')) {
            DB::statement("
                UPDATE presensis p
                JOIN pegawais pg ON pg.id = p.pegawai_id
                SET p.user_id = pg.user_id
                WHERE p.user_id IS NULL AND pg.user_id IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('presensis', 'user_id')) {
            Schema::table('presensis', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};

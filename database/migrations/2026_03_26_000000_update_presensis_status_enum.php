<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('presensis') || !Schema::hasColumn('presensis', 'status')) {
            return;
        }

        // Support current app validation values + legacy 'absen'
        DB::statement("ALTER TABLE `presensis` MODIFY `status` ENUM('hadir','terlambat','izin','sakit','alpha','absen') NOT NULL DEFAULT 'hadir'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('presensis') || !Schema::hasColumn('presensis', 'status')) {
            return;
        }

        // Revert to previous known enum (best-effort)
        DB::statement("ALTER TABLE `presensis` MODIFY `status` ENUM('hadir','absen','izin','sakit') NOT NULL DEFAULT 'hadir'");
    }
};

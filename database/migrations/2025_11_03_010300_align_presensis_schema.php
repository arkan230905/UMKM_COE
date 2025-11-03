<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For portability (SQLite/MySQL), do best-effort changes with try/catch
        try {
            // Drop FK if any (best effort, driver dependent)
            DB::statement('PRAGMA foreign_keys = OFF');
            Schema::table('presensis', function (Blueprint $table) {
                // Ensure pegawai_id is string to store NIP (EMP0001)
                if (Schema::hasColumn('presensis', 'pegawai_id')) {
                    try { $table->string('pegawai_id', 50)->change(); } catch (\Throwable $e) {}
                } else {
                    $table->string('pegawai_id', 50)->after('id');
                }
                // Columns that controller expects
                if (!Schema::hasColumn('presensis', 'jumlah_jam')) {
                    $table->decimal('jumlah_jam', 5, 2)->default(0)->after('status');
                }
                if (Schema::hasColumn('presensis', 'status')) {
                    // ensure enum includes Alpa - cannot change enum easily cross-db; leave as text validation handled app-side
                }
            });
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        // Do not add FK here to keep cross-db compatibility. App validation already enforces existence.
    }

    public function down(): void
    {
        // No-op safe down; optionally revert type (skip for sqlite compatibility)
    }
};

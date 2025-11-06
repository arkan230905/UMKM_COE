<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use Laravel's portable FK toggles to support MySQL
        Schema::disableForeignKeyConstraints();
        // Best-effort drop FK if exists to allow column change
        try {
            Schema::table('presensis', function (Blueprint $table) {
                try { $table->dropForeign(['pegawai_id']); } catch (\Throwable $e) {}
            });
        } catch (\Throwable $e) {}
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
                // leave as-is
            }
        });
        Schema::enableForeignKeyConstraints();

        // Do not add FK here to keep cross-db compatibility. App validation already enforces existence.
    }

    public function down(): void
    {
        // No-op safe down; optionally revert type (skip for sqlite compatibility)
    }
};

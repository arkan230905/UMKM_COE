<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix unique constraint on jabatans table to include user_id for multi-tenant isolation
     */
    public function up(): void
    {
        // Tambah user_id ke jabatans jika belum ada
        if (!Schema::hasColumn('jabatans', 'user_id')) {
            Schema::table('jabatans', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            });
        }

        // Drop old unique constraint if exists
        $indexes = DB::select("SHOW INDEX FROM jabatans WHERE Key_name = 'jabatans_kode_jabatan_unique'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE jabatans DROP INDEX jabatans_kode_jabatan_unique");
        }

        // Drop if already exists to avoid duplicate
        $existing = DB::select("SHOW INDEX FROM jabatans WHERE Key_name = 'jabatans_kode_user_unique'");
        if (empty($existing)) {
            DB::statement("ALTER TABLE jabatans ADD UNIQUE KEY jabatans_kode_user_unique (kode_jabatan, user_id)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop multi-tenant unique constraint
        $indexes = DB::select("SHOW INDEX FROM jabatans WHERE Key_name = 'jabatans_kode_user_unique'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE jabatans DROP INDEX jabatans_kode_user_unique");
        }
        
        // Restore old unique constraint
        DB::statement("ALTER TABLE jabatans ADD UNIQUE KEY jabatans_kode_jabatan_unique (kode_jabatan)");
    }
};

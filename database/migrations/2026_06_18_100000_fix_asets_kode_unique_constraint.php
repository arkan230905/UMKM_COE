<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix asets kode_aset unique constraint for multi-tenant.
     * 
     * Problem: 
     * - Old constraint: UNIQUE(kode_aset) - global unique
     * - New constraint: UNIQUE(user_id, kode_aset) - per-user unique
     * - Both exist causing duplicate entry error
     * 
     * Solution:
     * - Drop old global unique constraint
     * - Keep per-user unique constraint
     */
    public function up(): void
    {
        // Drop all old kode_aset unique constraints
        $constraints = [
            'asets_kode_aset_unique',
            'asets_kode_unique',
            'kode_aset_unique',
            'unique_aset_per_company'
        ];

        foreach ($constraints as $constraint) {
            try {
                $exists = DB::select("SHOW INDEX FROM asets WHERE Key_name = '{$constraint}'");
                if (!empty($exists)) {
                    DB::statement("ALTER TABLE asets DROP INDEX {$constraint}");
                    \Log::info("✅ Dropped constraint: {$constraint}");
                }
            } catch (\Exception $e) {
                // Constraint doesn't exist, skip
                \Log::info("⚠️  Constraint {$constraint} not found, skipping");
            }
        }

        // Ensure per-user unique constraint exists
        try {
            $exists = DB::select("SHOW INDEX FROM asets WHERE Key_name = 'asets_user_kode_unique'");
            if (empty($exists)) {
                Schema::table('asets', function (Blueprint $table) {
                    $table->unique(['user_id', 'kode_aset'], 'asets_user_kode_unique');
                });
                \Log::info("✅ Created per-user unique constraint: asets_user_kode_unique");
            } else {
                \Log::info("✅ Per-user unique constraint already exists");
            }
        } catch (\Exception $e) {
            \Log::error("❌ Failed to create per-user constraint: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop per-user constraint
        try {
            $exists = DB::select("SHOW INDEX FROM asets WHERE Key_name = 'asets_user_kode_unique'");
            if (!empty($exists)) {
                Schema::table('asets', function (Blueprint $table) {
                    $table->dropUnique('asets_user_kode_unique');
                });
            }
        } catch (\Exception $e) {
            // Ignore
        }

        // Restore global unique (for backward compatibility)
        try {
            Schema::table('asets', function (Blueprint $table) {
                $table->unique('kode_aset', 'asets_kode_aset_unique');
            });
        } catch (\Exception $e) {
            // Ignore
        }
    }
};

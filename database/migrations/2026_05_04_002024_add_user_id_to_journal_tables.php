<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add user_id to journal_entries table
        if (!Schema::hasColumn('journal_entries', 'user_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
            });
        }

        // Add user_id to jurnal_umum table
        if (!Schema::hasColumn('jurnal_umum', 'user_id')) {
            Schema::table('jurnal_umum', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
            });
        }

        // Backfill: set user_id from created_by in jurnal_umum
        DB::statement('UPDATE jurnal_umum SET user_id = created_by WHERE user_id IS NULL AND created_by IS NOT NULL');

        // Backfill journal_entries from related transactions
        // Set user_id based on ref_type and ref_id
        DB::statement("
            UPDATE journal_entries je
            JOIN pembelians p ON je.ref_type = 'purchase' AND je.ref_id = p.id
            SET je.user_id = p.user_id
            WHERE je.user_id IS NULL AND p.user_id IS NOT NULL
        ");

        DB::statement("
            UPDATE journal_entries je
            JOIN penjualans pj ON je.ref_type = 'sale' AND je.ref_id = pj.id
            SET je.user_id = pj.user_id
            WHERE je.user_id IS NULL AND pj.user_id IS NOT NULL
        ");

        DB::statement("
            UPDATE journal_entries je
            JOIN produksis pr ON je.ref_type IN ('production', 'production_material', 'production_labor', 'production_overhead', 'production_finished') AND je.ref_id = pr.id
            SET je.user_id = pr.user_id
            WHERE je.user_id IS NULL AND pr.user_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        if (Schema::hasColumn('journal_entries', 'user_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('jurnal_umum', 'user_id')) {
            Schema::table('jurnal_umum', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};

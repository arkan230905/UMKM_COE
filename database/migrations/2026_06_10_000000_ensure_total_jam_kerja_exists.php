<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * CRITICAL FIX: Ensure total_jam_kerja column exists in penggajians table.
     * This migration fixes "Column not found: 1054 Unknown column 'total_jam_kerja'" error
     * that occurs when production database is out of sync.
     */
    public function up(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('penggajians', 'total_jam_kerja')) {
                $table->decimal('total_jam_kerja', 8, 2)
                    ->default(0)
                    ->after('potongan')
                    ->comment('Total jam kerja (untuk sistem jam-based, 0 untuk produk-based)');
                    
                \Log::info('✅ Migration: Added total_jam_kerja column to penggajians table');
            } else {
                \Log::info('ℹ️  Migration: total_jam_kerja column already exists, skipping');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            if (Schema::hasColumn('penggajians', 'total_jam_kerja')) {
                $table->dropColumn('total_jam_kerja');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip if column doesn't exist
        if (!Schema::hasColumn('orders', 'payment_method')) {
            return;
        }

        // Altering ENUM is problematic with Laravel's standard methods, so we use raw SQL
        // to widen the column type to VARCHAR so it can accept 'tunai' and others easily.
        try {
            DB::statement('ALTER TABLE `orders` MODIFY COLUMN `payment_method` VARCHAR(50) NULL');
        } catch (\Exception $e) {
            // If modification fails, log but don't fail the migration
            \Log::warning('Failed to modify payment_method column: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip if column doesn't exist
        if (!Schema::hasColumn('orders', 'payment_method')) {
            return;
        }

        try {
            DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('qris', 'va_bca', 'va_bni', 'va_bri', 'va_mandiri', 'transfer', 'cod', 'kasir', 'tunai') NULL");
        } catch (\Exception $e) {
            // If modification fails, log but don't fail the migration
            \Log::warning('Failed to rollback payment_method column: ' . $e->getMessage());
        }
    }
};

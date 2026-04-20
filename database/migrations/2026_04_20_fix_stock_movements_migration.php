<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // This migration fixes the duplicate column issue
        // The keterangan column already exists, so we just update the enum
        
        // Update enum to include 'support' for bahan_pendukung
        if (Schema::hasTable('stock_movements')) {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN item_type ENUM('material', 'product', 'support')");
        }
    }

    public function down(): void
    {
        // Revert enum back to original
        if (Schema::hasTable('stock_movements')) {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN item_type ENUM('material', 'product')");
        }
    }
};

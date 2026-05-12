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
        // Update penjualans table
        DB::statement("ALTER TABLE penjualans MODIFY COLUMN payment_method ENUM('cash', 'transfer', 'credit') NOT NULL DEFAULT 'cash'");
        
        // Update pembelians table
        DB::statement("ALTER TABLE pembelians MODIFY COLUMN payment_method ENUM('cash', 'transfer', 'credit') NOT NULL DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old enum
        DB::statement("ALTER TABLE penjualans MODIFY COLUMN payment_method ENUM('cash', 'credit') NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE pembelians MODIFY COLUMN payment_method ENUM('cash', 'credit') NOT NULL DEFAULT 'cash'");
    }
};

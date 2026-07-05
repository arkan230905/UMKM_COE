<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE returs MODIFY COLUMN status ENUM('draft', 'approved', 'posted', 'rejected') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE returs MODIFY COLUMN status ENUM('draft', 'approved', 'posted') DEFAULT 'draft'");
    }
};

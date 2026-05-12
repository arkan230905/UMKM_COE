<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update status enum untuk produksis table
     */
    public function up(): void
    {
        // Update enum status untuk include nilai baru
        DB::statement("ALTER TABLE produksis MODIFY COLUMN status ENUM('draft', 'dalam_proses', 'selesai', 'pending', 'completed') DEFAULT 'draft'");
        
        // Update existing records
        DB::table('produksis')->where('status', 'pending')->update(['status' => 'draft']);
        DB::table('produksis')->where('status', 'completed')->update(['status' => 'selesai']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old enum values
        DB::statement("ALTER TABLE produksis MODIFY COLUMN status ENUM('pending', 'completed') DEFAULT 'pending'");
        
        // Update records back
        DB::table('produksis')->where('status', 'draft')->update(['status' => 'pending']);
        DB::table('produksis')->where('status', 'dalam_proses')->update(['status' => 'pending']);
        DB::table('produksis')->where('status', 'selesai')->update(['status' => 'completed']);
    }
};
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
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            // Check if foreign key constraint already exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'pelunasan_utangs' 
                AND COLUMN_NAME = 'pembelian_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            // Only add foreign key if it doesn't exist
            if (empty($foreignKeys)) {
                $table->foreign('pembelian_id')->references('id')->on('pembelians')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            // Drop foreign key constraint if it exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'pelunasan_utangs' 
                AND COLUMN_NAME = 'pembelian_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (!empty($foreignKeys)) {
                $table->dropForeign(['pembelian_id']);
            }
        });
    }
};
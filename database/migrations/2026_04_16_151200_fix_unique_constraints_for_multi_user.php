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
        // Fix bahan_pendukungs table - add composite unique constraint (user_id + kode_bahan)
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            // Check if unique constraint on kode_bahan exists first
            $indexes = DB::select("SHOW INDEX FROM bahan_pendukungs WHERE Key_name = 'bahan_pendukungs_kode_bahan_unique'");
            
            if (!empty($indexes)) {
                $table->dropUnique(['kode_bahan']);
            }
            
            // Add composite unique constraint (user_id + kode_bahan)
            $table->unique(['user_id', 'kode_bahan'], 'bahan_pendukungs_user_kode_unique');
        });
        
        // Fix bahan_bakus table - add composite unique constraint (user_id + kode_bahan)
        Schema::table('bahan_bakus', function (Blueprint $table) {
            // Check if unique constraint exists first
            $indexes = DB::select("SHOW INDEX FROM bahan_bakus WHERE Key_name = 'bahan_bakus_kode_bahan_unique'");
            
            if (!empty($indexes)) {
                $table->dropUnique(['kode_bahan']);
            }
            
            // Add composite unique constraint (user_id + kode_bahan)
            $table->unique(['user_id', 'kode_bahan'], 'bahan_bakus_user_kode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert bahan_pendukungs table
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'kode_bahan']);
            $table->unique('kode_bahan');
        });
        
        // Revert bahan_bakus table
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'kode_bahan']);
            $table->unique('kode_bahan');
        });
    }
};
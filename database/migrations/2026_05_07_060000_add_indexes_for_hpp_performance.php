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
        // Add indexes to improve query performance for HPP page
        
        // BiayaBahanBaku - frequently queried by produk_id and user_id
        Schema::table('biaya_bahan_bakus', function (Blueprint $table) {
            $table->index(['produk_id', 'user_id'], 'idx_bbb_produk_user');
            $table->index('bahan_baku_id', 'idx_bbb_bahan_baku');
        });
        
        // ProsesProduksi - frequently queried by user_id
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->index('user_id', 'idx_proses_user');
        });
        
        // BopProses - frequently queried by user_id and is_active
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->index(['user_id', 'is_active'], 'idx_bop_user_active');
        });
        
        // BahanBaku - frequently joined
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->index('user_id', 'idx_bahan_baku_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biaya_bahan_bakus', function (Blueprint $table) {
            $table->dropIndex('idx_bbb_produk_user');
            $table->dropIndex('idx_bbb_bahan_baku');
        });
        
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->dropIndex('idx_proses_user');
        });
        
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->dropIndex('idx_bop_user_active');
        });
        
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropIndex('idx_bahan_baku_user');
        });
    }
};

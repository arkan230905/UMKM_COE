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
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'user_id')) {
                $table->foreignId('user_id')->after('id')->nullable()->constrained('users')->onDelete('cascade');
                $table->index(['user_id', 'item_type', 'item_id']);
            }
        });

        // Update existing records with user_id from related tables
        DB::statement("
            UPDATE stock_movements sm
            LEFT JOIN pembelians p ON sm.ref_type = 'purchase' AND sm.ref_id = p.id
            LEFT JOIN penjualans pj ON sm.ref_type = 'sale' AND sm.ref_id = pj.id
            LEFT JOIN produksis pr ON sm.ref_type = 'production' AND sm.ref_id = pr.id
            SET sm.user_id = COALESCE(p.user_id, pj.user_id, pr.user_id, 1)
            WHERE sm.user_id IS NULL
        ");

        // Make user_id NOT NULL after updating existing records
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'user_id')) {
                $table->foreignId('user_id')->nullable(false)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id', 'item_type', 'item_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};

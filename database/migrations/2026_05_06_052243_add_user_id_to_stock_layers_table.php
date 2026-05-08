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
        Schema::table('stock_layers', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_layers', 'user_id')) {
                $table->foreignId('user_id')->after('id')->nullable()->constrained('users')->onDelete('cascade');
                $table->index(['user_id', 'item_type', 'item_id']);
            }
        });

        // Update existing records with user_id from related tables
        DB::statement("
            UPDATE stock_layers sl
            LEFT JOIN pembelians p ON sl.ref_type = 'purchase' AND sl.ref_id = p.id
            LEFT JOIN penjualans pj ON sl.ref_type = 'sale' AND sl.ref_id = pj.id
            LEFT JOIN produksis pr ON sl.ref_type = 'production' AND sl.ref_id = pr.id
            SET sl.user_id = COALESCE(p.user_id, pj.user_id, pr.user_id, 1)
            WHERE sl.user_id IS NULL
        ");

        // Make user_id NOT NULL after updating existing records
        Schema::table('stock_layers', function (Blueprint $table) {
            if (Schema::hasColumn('stock_layers', 'user_id')) {
                $table->foreignId('user_id')->nullable(false)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_layers', function (Blueprint $table) {
            if (Schema::hasColumn('stock_layers', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id', 'item_type', 'item_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};

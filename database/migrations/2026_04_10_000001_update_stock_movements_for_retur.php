<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add keterangan field for retur descriptions if it doesn't exist
        if (!Schema::hasColumn('stock_movements', 'keterangan')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('keterangan', 255)->nullable()->after('ref_id');
            });
        }

        // Update enum to include 'support' for bahan_pendukung
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN item_type ENUM('material', 'product', 'support')");
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });

        // Revert enum back to original
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN item_type ENUM('material', 'product')");
    }
};
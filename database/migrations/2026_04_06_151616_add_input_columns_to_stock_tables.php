<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing columns to stock_movements table
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'qty_as_input')) {
                $table->decimal('qty_as_input', 15, 4)->nullable()->after('ref_id');
            }
            if (!Schema::hasColumn('stock_movements', 'satuan_as_input')) {
                $table->string('satuan_as_input', 50)->nullable()->after('qty_as_input');
            }
        });

        // Add missing columns to stock_layers table
        Schema::table('stock_layers', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_layers', 'qty_as_input')) {
                $table->decimal('qty_as_input', 15, 4)->nullable()->after('ref_id');
            }
            if (!Schema::hasColumn('stock_layers', 'satuan_as_input')) {
                $table->string('satuan_as_input', 50)->nullable()->after('qty_as_input');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['qty_as_input', 'satuan_as_input']);
        });

        Schema::table('stock_layers', function (Blueprint $table) {
            $table->dropColumn(['qty_as_input', 'satuan_as_input']);
        });
    }
};
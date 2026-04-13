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
        // Check if keterangan column doesn't exist before adding
        if (!Schema::hasColumn('stock_movements', 'keterangan')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('ref_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if keterangan column exists before dropping
        if (Schema::hasColumn('stock_movements', 'keterangan')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }
    }
};

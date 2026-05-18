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
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('ref_id');
            }
            
            if (!Schema::hasColumn('stock_movements', 'manual_conversion_data')) {
                $table->json('manual_conversion_data')->nullable()->after('keterangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
            
            if (Schema::hasColumn('stock_movements', 'manual_conversion_data')) {
                $table->dropColumn('manual_conversion_data');
            }
        });
    }
};

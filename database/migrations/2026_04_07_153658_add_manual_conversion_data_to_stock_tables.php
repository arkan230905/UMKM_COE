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
        // Add manual_conversion_data column to stock_movements table
        if (!Schema::hasColumn('stock_movements', 'manual_conversion_data')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->json('manual_conversion_data')->nullable();
            });
        }

        // Add manual_conversion_data column to stock_layers table
        if (!Schema::hasColumn('stock_layers', 'manual_conversion_data')) {
            Schema::table('stock_layers', function (Blueprint $table) {
                $table->json('manual_conversion_data')->nullable();
            });
        }

        // Add jumlah_satuan_utama column to pembelian_details table if not exists
        if (!Schema::hasColumn('pembelian_details', 'jumlah_satuan_utama')) {
            Schema::table('pembelian_details', function (Blueprint $table) {
                $table->decimal('jumlah_satuan_utama', 15, 4)->nullable()->after('faktor_konversi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('manual_conversion_data');
        });

        Schema::table('stock_layers', function (Blueprint $table) {
            $table->dropColumn('manual_conversion_data');
        });

        Schema::table('pembelian_details', function (Blueprint $table) {
            $table->dropColumn('jumlah_satuan_utama');
        });
    }
};
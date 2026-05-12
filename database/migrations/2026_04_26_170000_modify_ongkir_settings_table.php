<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ongkir_settings', function (Blueprint $table) {
            // Drop old columns only if they exist
            if (Schema::hasColumn('ongkir_settings', 'range_berat_min')) {
                $table->dropColumn('range_berat_min');
            }
            if (Schema::hasColumn('ongkir_settings', 'range_berat_max')) {
                $table->dropColumn('range_berat_max');
            }
            if (Schema::hasColumn('ongkir_settings', 'harga_per_kg')) {
                $table->dropColumn('harga_per_kg');
            }
            if (Schema::hasColumn('ongkir_settings', 'minimal_ongkir')) {
                $table->dropColumn('minimal_ongkir');
            }
        });
        
        Schema::table('ongkir_settings', function (Blueprint $table) {
            // Add new columns only if they don't exist
            if (!Schema::hasColumn('ongkir_settings', 'jarak_min')) {
                $table->decimal('jarak_min', 8, 2)->default(0)->comment('dalam km');
            }
            if (!Schema::hasColumn('ongkir_settings', 'jarak_max')) {
                $table->decimal('jarak_max', 8, 2)->nullable()->comment('dalam km, null = tidak terbatas');
            }
            if (!Schema::hasColumn('ongkir_settings', 'harga_ongkir')) {
                $table->decimal('harga_ongkir', 15, 2)->default(0);
            }
        });
        
        // Modify status column
        Schema::table('ongkir_settings', function (Blueprint $table) {
            if (Schema::hasColumn('ongkir_settings', 'status')) {
                $table->dropColumn('status');
            }
        });
        
        Schema::table('ongkir_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('ongkir_settings', 'status')) {
                $table->boolean('status')->default(true)->comment('true = aktif, false = nonaktif');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ongkir_settings', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn(['jarak_min', 'jarak_max', 'harga_ongkir', 'status']);
            
            // Restore old columns
            $table->decimal('range_berat_min', 8, 2)->default(0);
            $table->decimal('range_berat_max', 8, 2)->nullable()->comment('null = tidak terbatas');
            $table->decimal('harga_per_kg', 15, 2)->default(0);
            $table->decimal('minimal_ongkir', 15, 2)->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
        });
    }
};
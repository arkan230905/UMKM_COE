<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add catalog-specific fields to perusahaan table
     */
    public function up(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->text('catalog_description')->nullable()->after('telepon');
            $table->string('maps_link')->nullable()->after('catalog_description');
            $table->decimal('latitude', 10, 8)->nullable()->after('maps_link');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropColumn(['catalog_description', 'maps_link', 'latitude', 'longitude']);
        });
    }
};

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
        Schema::table('perusahaan', function (Blueprint $table) {
            // Background customization columns
            $table->enum('background_type', ['color', 'gradient', 'image'])->default('color')->after('catalog_description');
            $table->string('background_color', 7)->default('#ffffff')->after('background_type');
            $table->string('gradient_color_1', 7)->nullable()->after('background_color');
            $table->string('gradient_color_2', 7)->nullable()->after('gradient_color_1');
            $table->string('gradient_direction', 50)->default('to right')->after('gradient_color_2');
            $table->string('background_image')->nullable()->after('gradient_direction');
            $table->integer('background_opacity')->default(50)->after('background_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropColumn([
                'background_type',
                'background_color',
                'gradient_color_1',
                'gradient_color_2',
                'gradient_direction',
                'background_image',
                'background_opacity'
            ]);
        });
    }
};

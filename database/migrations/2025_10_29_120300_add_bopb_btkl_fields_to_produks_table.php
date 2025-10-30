<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (!Schema::hasColumn('produks', 'bopb_method')) {
                $table->string('bopb_method', 20)->nullable()->after('margin_percent'); // per_unit|per_hour
            }
            if (!Schema::hasColumn('produks', 'bopb_rate')) {
                $table->decimal('bopb_rate', 14, 2)->nullable()->after('bopb_method'); // cost per unit or per hour
            }
            if (!Schema::hasColumn('produks', 'labor_hours_per_unit')) {
                $table->decimal('labor_hours_per_unit', 8, 2)->nullable()->after('bopb_rate');
            }
            if (!Schema::hasColumn('produks', 'btkl_per_unit')) {
                $table->decimal('btkl_per_unit', 14, 2)->nullable()->after('labor_hours_per_unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            foreach (['bopb_method','bopb_rate','labor_hours_per_unit','btkl_per_unit'] as $col) {
                if (Schema::hasColumn('produks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

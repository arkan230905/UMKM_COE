<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (!Schema::hasColumn('asets', 'acquisition_cost')) {
                $table->decimal('acquisition_cost', 18, 2)->nullable()->after('harga');
            }
            if (!Schema::hasColumn('asets', 'residual_value')) {
                $table->decimal('residual_value', 18, 2)->nullable()->after('acquisition_cost');
            }
            if (!Schema::hasColumn('asets', 'useful_life_years')) {
                $table->integer('useful_life_years')->nullable()->after('residual_value');
            }
            if (!Schema::hasColumn('asets', 'depr_start_date')) {
                $table->date('depr_start_date')->nullable()->after('useful_life_years');
            }
            if (!Schema::hasColumn('asets', 'depr_method')) {
                $table->string('depr_method', 16)->nullable()->after('depr_start_date'); // 'SL'
            }
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // keep columns; do not drop to avoid data loss
        });
    }
};

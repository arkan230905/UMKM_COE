<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('boms', function (Blueprint $table) {
            $table->decimal('btkl_per_unit', 15, 2)->default(0)->after('total_biaya');
            $table->decimal('bop_rate', 10, 4)->default(0)->after('btkl_per_unit');
            $table->decimal('bop_per_unit', 15, 2)->default(0)->after('bop_rate');
            $table->decimal('total_btkl', 15, 2)->default(0)->after('bop_per_unit');
            $table->decimal('total_bop', 15, 2)->default(0)->after('total_btkl');
            $table->string('periode', 7)->nullable()->after('total_bop');
        });
    }

    public function down()
    {
        Schema::table('boms', function (Blueprint $table) {
            $table->dropColumn([
                'btkl_per_unit',
                'bop_rate',
                'bop_per_unit',
                'total_btkl',
                'total_bop',
                'periode'
            ]);
        });
    }
};

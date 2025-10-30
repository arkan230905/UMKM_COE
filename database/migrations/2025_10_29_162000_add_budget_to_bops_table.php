<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bops', function (Blueprint $table) {
            $table->decimal('budget', 15, 2)->default(0)->after('nominal');
            $table->string('periode', 7)->nullable()->after('budget');
            $table->boolean('is_active')->default(true)->after('periode');
        });
    }

    public function down()
    {
        Schema::table('bops', function (Blueprint $table) {
            $table->dropColumn(['budget', 'periode', 'is_active']);
        });
    }
};

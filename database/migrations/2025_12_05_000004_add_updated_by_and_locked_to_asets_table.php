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
        Schema::table('asets', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->nullable()->after('keterangan')->comment('User who last updated the asset');
            $table->boolean('locked')->default(false)->after('updated_by')->comment('Lock asset from editing after depreciation generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->dropColumn(['locked', 'updated_by']);
        });
    }
};

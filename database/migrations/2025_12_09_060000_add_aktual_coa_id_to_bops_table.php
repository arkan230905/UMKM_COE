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
        Schema::table('bops', function (Blueprint $table) {
            if (!Schema::hasColumn('bops', 'aktual')) {
                $table->decimal('aktual', 15, 2)->default(0)->after('budget');
            }
            if (!Schema::hasColumn('bops', 'coa_id')) {
                $table->unsignedBigInteger('coa_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bops', function (Blueprint $table) {
            if (Schema::hasColumn('bops', 'aktual')) {
                $table->dropColumn('aktual');
            }
            if (Schema::hasColumn('bops', 'coa_id')) {
                $table->dropColumn('coa_id');
            }
        });
    }
};

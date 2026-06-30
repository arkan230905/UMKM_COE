<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'company_address')) $table->text('company_address')->nullable()->after('catatan');
            if (!Schema::hasColumn('orders', 'company_latitude')) $table->string('company_latitude')->nullable()->after('company_address');
            if (!Schema::hasColumn('orders', 'company_longitude')) $table->string('company_longitude')->nullable()->after('company_latitude');
            if (!Schema::hasColumn('orders', 'distance_km')) $table->decimal('distance_km', 10, 2)->nullable()->after('company_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'company_address',
                'company_latitude',
                'company_longitude',
                'distance_km',
            ]);
        });
    }
};

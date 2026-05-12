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
        Schema::table('jabatans', function (Blueprint $table) {
            $table->decimal('tunjangan_transport', 15, 2)->default(0)->after('tunjangan');
            $table->decimal('tunjangan_konsumsi', 15, 2)->default(0)->after('tunjangan_transport');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropColumn(['tunjangan_transport', 'tunjangan_konsumsi']);
        });
    }
};

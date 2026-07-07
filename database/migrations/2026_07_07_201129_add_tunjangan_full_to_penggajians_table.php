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
        Schema::table('penggajians', function (Blueprint $table) {
            $table->decimal('tunjangan_transport_full', 15, 2)->default(0)->after('tunjangan_transport');
            $table->decimal('tunjangan_konsumsi_full', 15, 2)->default(0)->after('tunjangan_konsumsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->dropColumn(['tunjangan_transport_full', 'tunjangan_konsumsi_full']);
        });
    }
};

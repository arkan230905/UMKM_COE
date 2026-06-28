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
        Schema::table('pembelians', function (Blueprint $table) {
            $table->decimal('dp', 15, 2)->default(0)->after('total_harga')->comment('Down Payment untuk pembelian kredit');
            $table->date('tanggal_jatuh_tempo')->nullable()->after('dp')->comment('Tanggal jatuh tempo untuk pembelian kredit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn(['dp', 'tanggal_jatuh_tempo']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('penjualan_id')->nullable()->after('paid_at');
            
            $table->foreign('penjualan_id')->references('id')->on('penjualans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['penjualan_id']);
            $table->dropColumn('penjualan_id');
        });
    }
};

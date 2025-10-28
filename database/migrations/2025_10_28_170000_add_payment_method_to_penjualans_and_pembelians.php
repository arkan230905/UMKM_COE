<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualans', 'payment_method')) {
                $table->string('payment_method', 20)->default('cash')->after('tanggal'); // cash|credit
            }
        });
        Schema::table('pembelians', function (Blueprint $table) {
            if (!Schema::hasColumn('pembelians', 'payment_method')) {
                $table->string('payment_method', 20)->default('cash')->after('tanggal'); // cash|credit
            }
        });
    }

    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('penjualans', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
        Schema::table('pembelians', function (Blueprint $table) {
            if (Schema::hasColumn('pembelians', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};

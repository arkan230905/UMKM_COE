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
            if (!Schema::hasColumn('pembelians', 'dp_payment_method_id')) {
                $table->foreignId('dp_payment_method_id')
                    ->nullable()
                    ->after('dp')
                    ->comment('Akun pembayaran untuk DP')
                    ->constrained('coas')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            if (Schema::hasColumn('pembelians', 'dp_payment_method_id')) {
                $table->dropForeign(['dp_payment_method_id']);
                $table->dropColumn('dp_payment_method_id');
            }
        });
    }
};

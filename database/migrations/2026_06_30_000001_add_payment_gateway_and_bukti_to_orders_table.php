<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_gateway')) {
                $table->string('payment_gateway')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'bukti_pembayaran')) {
                $table->string('bukti_pembayaran')->nullable()->after('payment_gateway');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_gateway', 'bukti_pembayaran']);
        });
    }
};

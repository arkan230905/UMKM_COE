<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'bank_va')) {
                $table->string('bank_va')->nullable()->after('bukti_pembayaran');
            }
            if (!Schema::hasColumn('orders', 'nomor_va')) {
                $table->string('nomor_va')->nullable()->after('bank_va');
            }
            if (!Schema::hasColumn('orders', 'expiry_time')) {
                $table->string('expiry_time')->nullable()->after('nomor_va');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['bank_va', 'nomor_va', 'expiry_time']);
        });
    }
};

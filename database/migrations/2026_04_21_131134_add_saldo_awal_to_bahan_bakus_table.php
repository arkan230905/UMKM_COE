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
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->decimal('saldo_awal', 15, 4)->default(0)->after('stok')->comment('Stok awal bahan baku');
            $table->date('tanggal_saldo_awal')->nullable()->after('saldo_awal')->comment('Tanggal saldo awal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropColumn(['saldo_awal', 'tanggal_saldo_awal']);
        });
    }
};

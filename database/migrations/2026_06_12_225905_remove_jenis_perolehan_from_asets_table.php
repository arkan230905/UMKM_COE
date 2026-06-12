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
        Schema::table('asets', function (Blueprint $table) {
            $table->dropColumn(['jenis_perolehan', 'sumber_dana_coa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->enum('jenis_perolehan', ['pembelian_baru', 'saldo_awal'])->nullable();
            $table->unsignedBigInteger('sumber_dana_coa_id')->nullable();
            $table->foreign('sumber_dana_coa_id')->references('id')->on('coas');
        });
    }
};

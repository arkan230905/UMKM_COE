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
        Schema::create('coa_period_balances', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun', 50);
            $table->unsignedBigInteger('period_id');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('saldo_akhir', 15, 2)->default(0);
            $table->boolean('is_posted')->default(false);
            $table->timestamps();
            
            $table->unique(['kode_akun', 'period_id']);
            $table->index('period_id');
            $table->index('is_posted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa_period_balances');
    }
};

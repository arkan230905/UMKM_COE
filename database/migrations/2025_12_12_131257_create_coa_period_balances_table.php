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
            $table->string('kode_akun'); // Foreign key ke coas.kode_akun
            $table->foreignId('period_id')->constrained('coa_periods');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('saldo_akhir', 15, 2)->default(0);
            $table->boolean('is_posted')->default(false);
            $table->timestamps();
            
            // Unique constraint untuk kode_akun dan period_id
            $table->unique(['kode_akun', 'period_id']);
            
            // Foreign key constraint ke coas
            $table->foreign('kode_akun')->references('kode_akun')->on('coas');
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

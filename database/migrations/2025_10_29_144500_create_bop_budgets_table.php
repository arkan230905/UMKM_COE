<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bop_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coa_id')->constrained('coas');
            $table->decimal('jumlah_budget', 15, 2);
            $table->string('periode', 7); // Format: YYYY-MM
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Pastikan tidak ada duplikasi budget untuk COA yang sama di periode yang sama
            $table->unique(['coa_id', 'periode']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bop_budgets');
    }
};

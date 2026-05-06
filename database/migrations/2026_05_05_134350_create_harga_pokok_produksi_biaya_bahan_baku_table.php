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
        Schema::create('harga_pokok_produksi_biaya_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('bom_job_bbb_id');
            $table->timestamps();
            
            $table->index(['user_id', 'bom_job_bbb_id'], 'hpp_bbb_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_pokok_produksi_biaya_bahan_baku');
    }
};

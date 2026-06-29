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
        Schema::create('target_produksi_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_produksi_id')->index();
            $table->unsignedTinyInteger('bulan')->comment('1-12 untuk Januari-Desember');
            $table->unsignedInteger('target_bulanan')->default(0);
            $table->timestamps();
            
            // Foreign key
            $table->foreign('target_produksi_id')
                ->references('id')
                ->on('target_produksi')
                ->onDelete('cascade');
            
            // Unique constraint: satu bulan hanya boleh ada sekali per target
            $table->unique(['target_produksi_id', 'bulan'], 'unique_bulan_per_target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_produksi_detail');
    }
};

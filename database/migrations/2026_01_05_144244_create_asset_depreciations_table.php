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
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->integer('tahun');
            $table->decimal('beban_penyusutan', 15, 2);
            $table->decimal('akumulasi_penyusutan', 15, 2);
            $table->decimal('nilai_buku_akhir', 15, 2);
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('asset_id')->references('id')->on('asets')->onDelete('cascade');
            
            // Unique constraint untuk setiap asset per tahun
            $table->unique(['asset_id', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};

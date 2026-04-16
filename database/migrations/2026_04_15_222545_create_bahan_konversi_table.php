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
        Schema::create('bahan_konversi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_id')->constrained('bahan_pendukungs')->onDelete('cascade');
            $table->foreignId('satuan_id')->constrained('satuans')->onDelete('restrict');
            $table->decimal('nilai', 15, 6)->comment('Nilai konversi dari satuan utama');
            $table->timestamps();
            
            $table->unique(['bahan_id', 'satuan_id']);
            $table->index('bahan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_konversi');
    }
};

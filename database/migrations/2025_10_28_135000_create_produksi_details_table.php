<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produksi_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produksi_id')->constrained('produksis')->onDelete('cascade');
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('cascade');
            $table->decimal('qty_resep', 15, 4)->default(0);
            $table->string('satuan_resep', 50)->nullable();
            $table->decimal('qty_konversi', 15, 4)->default(0);
            $table->decimal('harga_satuan', 15, 4)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_details');
    }
};

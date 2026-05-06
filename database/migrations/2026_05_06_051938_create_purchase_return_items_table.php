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
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('purchase_return_id')->constrained('purchase_returns')->onDelete('cascade');
            $table->foreignId('pembelian_detail_id')->nullable()->constrained('pembelian_details')->onDelete('set null');
            $table->foreignId('bahan_baku_id')->nullable()->constrained('bahan_bakus')->onDelete('set null');
            $table->foreignId('bahan_pendukung_id')->nullable()->constrained('bahan_pendukungs')->onDelete('set null');
            $table->string('unit', 50);
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
            
            $table->index(['user_id', 'purchase_return_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
    }
};

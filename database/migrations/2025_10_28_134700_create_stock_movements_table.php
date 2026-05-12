<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->enum('item_type', ['material', 'product']);
            $table->unsignedBigInteger('item_id');
            $table->date('tanggal');
            $table->enum('direction', ['in', 'out']);
            $table->decimal('qty', 15, 4);
            $table->string('satuan', 50)->nullable();
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->string('ref_type', 50)->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->timestamps();
            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

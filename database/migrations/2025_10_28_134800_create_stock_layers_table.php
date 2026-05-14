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
        Schema::create('stock_layers', function (Blueprint $table) {
            $table->id();
            // Menambahkan user_id agar data terikat pada owner (User)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->enum('item_type', ['material', 'product']);
            $table->unsignedBigInteger('item_id');
            $table->date('tanggal');
            $table->decimal('remaining_qty', 15, 4);
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->string('ref_type', 50)->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->timestamps();

            // Indexing untuk performa sistem
            $table->index('user_id');
            $table->index(['item_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_layers');
    }
};
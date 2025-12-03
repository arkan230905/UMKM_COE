<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penjualan_id');
            $table->string('return_number')->unique();
            $table->date('return_date');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_return_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, completed
            $table->timestamps();

            $table->foreign('penjualan_id')->references('id')->on('penjualans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};

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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            // Menambahkan user_id agar data terikat pada owner (User)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->unsignedBigInteger('pembelian_id');
            $table->string('return_number')->unique();
            $table->date('return_date');
            $table->string('reason')->nullable();
            $table->string('jenis_retur')->default('tukar_barang'); // tukar_barang, refund
            $table->text('notes')->nullable();
            $table->decimal('total_return_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, completed
            $table->timestamps();

            // Foreign key dan Index
            $table->foreign('pembelian_id')->references('id')->on('pembelians')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
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
        if (!Schema::hasTable('pembayaran_bebans')) {
            Schema::create('pembayaran_bebans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->date('tanggal');
                $table->string('keterangan');
                $table->decimal('jumlah', 15, 2);
                $table->unsignedBigInteger('beban_operasional_id')->nullable();
                $table->string('metode_pembayaran')->default('cash');
                $table->string('status')->default('paid');
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('beban_operasional_id')->references('id')->on('beban_operasional')->onDelete('cascade');
                
                echo "Created pembayaran_bebans table with user_id column\n";
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_bebans');
    }
};

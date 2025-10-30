<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('coa_beban_id');
            $table->string('metode_bayar')->default('cash'); // cash/bank
            $table->string('coa_kasbank')->default('101');   // kode akun kas/bank
            $table->decimal('nominal', 18, 2);
            $table->string('deskripsi')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_payments');
    }
};

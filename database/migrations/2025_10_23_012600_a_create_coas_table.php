<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun')->unique();
            $table->string('nama_akun');
            $table->enum('tipe_akun', ['Asset','Liability','Equity','Revenue','Expense']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coas');
    }
};

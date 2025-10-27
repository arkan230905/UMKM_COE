<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coa_id')->nullable();
            $table->string('nama_akun')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Foreign key (opsional, tapi bagus ditambah)
            $table->foreign('coa_id')->references('id')->on('coas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bops');
    }
};
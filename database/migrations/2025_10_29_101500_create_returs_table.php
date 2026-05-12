<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('returs')) {
            Schema::create('returs', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['sale','purchase']);
                $table->unsignedBigInteger('ref_id');
                $table->date('tanggal');
                $table->enum('kompensasi', ['refund','credit'])->default('credit');
                $table->enum('status', ['draft','approved','posted'])->default('draft');
                $table->text('alasan')->nullable();
                $table->text('memo')->nullable();
                $table->timestamps();
                $table->index(['type','ref_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('returs');
    }
};

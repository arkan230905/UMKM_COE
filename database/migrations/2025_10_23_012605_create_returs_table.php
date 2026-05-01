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
                $table->date('tanggal');
                $table->unsignedBigInteger('pembelian_id')->nullable();
                $table->decimal('jumlah', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('returs');
    }
};

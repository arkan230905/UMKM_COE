<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai_produk_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedBigInteger('produk_id');
            $table->decimal('allocation_pct', 5, 2)->default(0); // 0..100
            $table->timestamps();
            $table->unique(['pegawai_id','produk_id']);
            $table->index(['produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai_produk_allocations');
    }
};

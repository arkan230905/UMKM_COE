<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->date('tanggal');
            $table->decimal('qty_produksi', 15, 4);
            $table->decimal('total_bahan', 15, 2)->default(0);
            $table->decimal('total_btkl', 15, 2)->default(0);
            $table->decimal('total_bop', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksis');
    }
};

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
        Schema::create('bahan_pendukungs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bahan')->unique();
            $table->string('nama_bahan');
            $table->text('deskripsi')->nullable();
            $table->foreignId('satuan_id')->constrained('satuans')->onDelete('restrict');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('stok', 15, 4)->default(0);
            $table->decimal('stok_minimum', 15, 4)->default(0);
            $table->enum('kategori', ['gas', 'bumbu', 'minyak', 'air', 'listrik', 'pembersih', 'lainnya'])->default('lainnya');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('nama_bahan');
            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_pendukungs');
    }
};

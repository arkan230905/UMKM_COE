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
        // Drop existing table to avoid conflicts
        Schema::dropIfExists('returs');

        // Recreate with clean structure
        Schema::create('returs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_retur')->unique();
            $table->date('tanggal');
            $table->string('referensi_kode')->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->enum('tipe_kompensasi', ['barang', 'uang'])->default('barang');
            $table->decimal('total_nilai_retur', 15, 2)->default(0);
            $table->decimal('nilai_kompensasi', 15, 2)->default(0);
            $table->enum('status', ['draft', 'diproses', 'selesai'])->default('draft');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returs');
    }
};

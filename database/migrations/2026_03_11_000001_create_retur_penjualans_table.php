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
        Schema::create('retur_penjualans', function (Blueprint $table) {
            $table->id();
            // Menambahkan user_id agar data terikat pada owner
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->string('nomor_retur')->unique();
            $table->date('tanggal');
            
            // Relasi ke tabel penjualan dan pelanggan
            $table->foreignId('penjualan_id')->constrained('penjualans')->onDelete('cascade');
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggans')->onDelete('set null');
            
            $table->enum('jenis_retur', ['tukar_barang', 'refund', 'kredit']);
            $table->decimal('total_retur', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            
            // Status transaksi
            $table->string('status')->default('belum_dibayar'); // belum_dibayar, lunas, selesai
            
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Index untuk mempercepat pencarian data per user
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_penjualans');
    }
};
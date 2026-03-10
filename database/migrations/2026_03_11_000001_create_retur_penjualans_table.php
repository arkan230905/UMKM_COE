<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_retur')->unique();
            $table->date('tanggal');
            $table->foreignId('penjualan_id')->constrained('penjualans')->onDelete('cascade');
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggans')->onDelete('set null');
            $table->enum('jenis_retur', ['tukar_barang', 'refund', 'kredit']);
            $table->decimal('total_retur', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->string('status')->default('belum_dibayar'); // belum_dibayar, lunas, selesai
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_penjualans');
    }
};

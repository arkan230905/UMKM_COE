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
        // Pengaman agar tidak terjadi error "table already exists"
        if (!Schema::hasTable('ap_settlements')) {
            Schema::create('ap_settlements', function (Blueprint $table) {
                $table->id();
                
                // 1. Relasi Owner/User (Multi-tenant)
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                
                // 2. Data Pelunasan & Relasi
                $table->date('tanggal');
                $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
                $table->foreignId('pembelian_id')->constrained('pembelians')->onDelete('cascade');
                
                // 3. Nominal Keuangan (Menggunakan presisi 15, 2 sesuai standar modul lainnya)
                $table->decimal('total_tagihan', 15, 2);
                $table->decimal('diskon', 15, 2)->default(0);
                $table->decimal('denda_bunga', 15, 2)->default(0);
                $table->decimal('dibayar_bersih', 15, 2);
                
                // 4. Informasi Pembayaran
                $table->string('metode_bayar')->default('cash'); // cash/bank
                $table->string('coa_kasbank')->default('101');
                $table->text('keterangan')->nullable();
                $table->string('status')->default('lunas'); // lunas/parsial
                
                $table->timestamps();

                // 5. Indexing untuk performa laporan Owner
                $table->index('user_id');
                $table->index('vendor_id');
                $table->index('pembelian_id');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ap_settlements');
    }
};
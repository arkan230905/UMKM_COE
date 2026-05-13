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
        if (!Schema::hasTable('bop_proses')) {
            Schema::create('bop_proses', function (Blueprint $table) {
                $table->id();
                
                // Relasi Owner/User (Multi-tenant)
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                
                // Relasi Produk
                $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
                
                // Rincian Komponen BOP (Decimal 15,2)
                $table->decimal('listrik_per_produk', 15, 2)->default(0);
                $table->decimal('air_per_produk', 15, 2)->default(0);
                $table->decimal('gas_per_produk', 15, 2)->default(0);
                $table->decimal('penyusutan_per_produk', 15, 2)->default(0);
                $table->decimal('pemeliharaan_per_produk', 15, 2)->default(0);
                $table->decimal('lain_lain_per_produk', 15, 2)->default(0);
                
                $table->decimal('total_bop_per_produk', 15, 2)->default(0);
                
                $table->timestamps();

                // Optimasi Performa untuk pencatatan dan pelaporan HPP
                $table->index('user_id', 'idx_bop_user');
                $table->index('produk_id', 'idx_bop_produk');
                $table->index(['user_id', 'produk_id'], 'idx_bop_user_produk');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bop_proses');
    }
};
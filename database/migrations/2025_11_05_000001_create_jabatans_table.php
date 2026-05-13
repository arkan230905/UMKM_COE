<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void 
    {
        if (!Schema::hasTable('jabatans')) {
            Schema::create('jabatans', function (Blueprint $table) {
                $table->id();
                
                // Multi-tenant: Memisahkan data antar Owner
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                
                $table->string('kode_jabatan')->index(); 
                $table->string('nama');
                $table->string('kategori')->index(); // btkl
                
                $table->decimal('gaji_pokok', 15, 2)->default(0);
                $table->decimal('tunjangan', 15, 2)->default(0);
                $table->decimal('tunjangan_transport', 15, 2)->default(0);
                $table->decimal('tunjangan_konsumsi', 15, 2)->default(0);
                $table->decimal('asuransi', 15, 2)->default(0);
                
                // LOGIKA PER PRODUK
                $table->decimal('tarif', 15, 2)->default(0); 
                $table->decimal('tarif_per_produk', 15, 2)->default(0); 
                
                $table->text('deskripsi')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'kode_jabatan']);
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('jabatans');
    }
};
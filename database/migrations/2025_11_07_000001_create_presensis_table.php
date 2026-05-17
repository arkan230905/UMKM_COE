<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('presensis')) {
            Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            
            // 🔒 Multi-tenant isolation (Owner UMKM)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 
            
            // Relasi ke Pegawai (Gunakan foreignId agar standar Laravel)
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
            
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            
            // Kolom Jam Kerja (Decimal)
            $table->decimal('jumlah_jam', 5, 2)->default(0); 
            
            // Logika SIMACOST: Tracking hasil kerja unit/produk
            $table->integer('jumlah_produk_dihasilkan')->default(0); 
            
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
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
        // SKIP: Tabel accounts tidak digunakan, kita pakai tabel coas
        // Migrasi ini di-skip untuk menghindari konflik
        if (Schema::hasTable('accounts')) {
            return; // Skip jika tabel sudah ada
        }
        
        // Jika tabel belum ada, buat tabel accounts
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            // Kolom Identitas & Relasi
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // WAJIB ADA untuk Global Scope di Model Coa
            
            // Kolom Inti Akun
            $table->string('kode_akun')->unique();
            $table->string('nama_akun');
            $table->string('tipe_akun'); 
            $table->string('kategori_akun')->nullable();
            
            // Kolom Struktur & Hierarki (Biar migrasi lama 2026 aman)
            $table->string('kode_induk')->nullable(); 
            $table->boolean('is_akun_header')->default(false);
            
            // Kolom Saldo & Akuntansi
            $table->enum('saldo_normal', ['debit', 'kredit']);
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->date('tanggal_saldo_awal')->nullable();
            $table->boolean('posted_saldo_awal')->default(false);
            
            // Kolom Tambahan (Sesuai Fillable di Model Coa Abang)
            $table->string('nomor_rekening')->nullable();
            $table->string('atas_nama')->nullable();
            $table->text('keterangan')->nullable();
            
            $table->timestamps();

            // Indexing biar query kencang
            $table->index('company_id');
            $table->index('user_id');
            $table->index('kode_akun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            
            $table->string('kode_akun')->unique();
            $table->string('nama_akun');
            $table->string('tipe_akun'); 
            $table->string('kategori_akun')->nullable();
            
            // Kolom Tambahan agar migrasi lama (2026) tidak error
            $table->string('kode_induk')->nullable(); 
            $table->boolean('is_akun_header')->default(false);
            
            $table->enum('saldo_normal', ['debit', 'kredit']);
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->date('tanggal_saldo_awal')->nullable();
            $table->boolean('posted_saldo_awal')->default(false);
            $table->text('keterangan')->nullable();
            
            $table->timestamps();

            $table->index('company_id');
            $table->index('kode_akun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
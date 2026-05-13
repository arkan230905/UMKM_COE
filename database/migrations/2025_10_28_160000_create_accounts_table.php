<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan nama tabel adalah 'accounts' sesuai standar proyek SIMACOST
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            
            // Kolom untuk Multi-Tenant (Owner)
            $table->unsignedBigInteger('company_id')->nullable();
            
            // Struktur Utama COA
            $table->string('kode_akun')->unique();
            $table->string('nama_akun');
            $table->string('tipe_akun'); // Aset, Kewajiban, Modal, dll
            $table->enum('saldo_normal', ['debit', 'kredit']);
            
            // Saldo Awal WAJIB Manual (Default 0)
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->date('tanggal_saldo_awal')->nullable();
            
            $table->timestamps();

            // Indexing untuk performa
            $table->index('company_id');
            $table->index('kode_akun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
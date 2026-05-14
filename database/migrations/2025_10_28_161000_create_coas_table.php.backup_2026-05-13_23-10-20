<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coas', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenant: Relasi Owner/User dan Perusahaan
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('perusahaan')->onDelete('cascade');
            
            // Core Accounting Fields
            $table->string('kode_akun', 20);
            $table->string('nama_akun');
            $table->string('tipe_akun'); // Asset, Liability, Equity, Revenue, Expense, Beban, dll
            $table->string('kategori_akun', 50)->nullable();
            
            // Hierarchy Fields
            $table->boolean('is_akun_header')->default(false);
            $table->string('kode_induk', 20)->nullable();
            
            // Saldo Fields
            $table->enum('saldo_normal', ['debit', 'kredit'])->default('debit');
            
            // Saldo Awal (MANUAL - tidak otomatis)
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->date('tanggal_saldo_awal')->nullable();
            $table->boolean('posted_saldo_awal')->default(false);
            
            // Additional Fields
            $table->text('keterangan')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('atas_nama')->nullable();
            
            $table->timestamps();

            // Indexes untuk performa
            $table->index('company_id');
            $table->index('user_id');
            $table->index('kode_akun');
            
            // Unique constraint untuk multi-tenant
            // Kode akun harus unique per company
            $table->unique(['kode_akun', 'company_id'], 'coas_kode_company_unique');
        });
        
        // Foreign key untuk hierarchy (kode_induk)
        // Dibuat terpisah setelah tabel dibuat
        Schema::table('coas', function (Blueprint $table) {
            $table->foreign('kode_induk')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coas');
    }
};
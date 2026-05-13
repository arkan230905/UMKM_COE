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
            
            // Kolom untuk Multi-tenant/Owner
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->string('kode_akun');
            $table->string('nama_akun');
            $table->enum('tipe_akun', ['Asset','Liability','Equity','Revenue','Expense']);
            $table->timestamps();

            // INDEXING & CONSTRAINTS
            $table->index('user_id');
            
            /** 
             * CRITICAL: Tambahkan index pada kode_akun. 
             * Tanpa index ini, tabel lain (seperti coa_period_balances) akan GAGAL 
             * saat membuat foreign key ke kolom kode_akun.
             */
            $table->index('kode_akun'); 

            // Unique gabungan agar satu owner tidak memiliki kode akun yang sama
            $table->unique(['user_id', 'kode_akun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coas');
    }
};
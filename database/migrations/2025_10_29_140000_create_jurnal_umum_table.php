<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_umum', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke User
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            // Relasi ke Perusahaan (Sesuai log: create_perusahaan_table)
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('perusahaan')->onDelete('cascade');

            /**
             * PERBAIKAN UTAMA:
             * Berdasarkan log Anda, tabel yang ada adalah 'accounts'.
             * Kita gunakan coa_id tapi merujuk ke tabel 'accounts'.
             */
            $table->unsignedBigInteger('coa_id');
            $table->foreign('coa_id')->references('id')->on('accounts')->onDelete('cascade');
            
            $table->date('tanggal');
            $table->string('bukti_transaksi')->nullable();
            $table->string('keterangan');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            
            $table->timestamps();

            $table->index('company_id');
            $table->index('coa_id');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_umum');
    }
};
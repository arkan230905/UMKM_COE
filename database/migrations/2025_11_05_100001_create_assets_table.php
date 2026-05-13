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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            
            // --- PROTEKSI MULTI-TENANT ---
            // Menambahkan relasi ke perusahaan agar data aset terisolasi
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('perusahaan')->onDelete('cascade');
            
            $table->string('nama_aset');
            $table->date('tanggal_perolehan');
            $table->decimal('harga_perolehan', 15, 2);
            $table->decimal('nilai_sisa', 15, 2)->default(0);
            $table->unsignedInteger('umur_ekonomis'); // dalam tahun

            // --- PERBAIKAN FOREIGN KEY ---
            // Mengarahkan referensi ke tabel 'accounts' sesuai struktur SIMACOST
            $table->unsignedBigInteger('expense_coa_id')->nullable();
            $table->foreign('expense_coa_id')->references('id')->on('accounts')->onDelete('set null');

            $table->unsignedBigInteger('accum_depr_coa_id')->nullable();
            $table->foreign('accum_depr_coa_id')->references('id')->on('accounts')->onDelete('set null');

            $table->boolean('locked')->default(false); // mencegah hapus jika sudah ada transaksi
            $table->timestamps();

            // Index untuk optimasi query per tenant
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
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
        // Drop tabel lama jika ada agar rebuild bersih
        Schema::dropIfExists('asets');
        
        // Buat tabel baru dengan struktur lengkap dan dukungan Multi-Tenant
        Schema::create('asets', function (Blueprint $table) {
            $table->id();

            // --- PROTEKSI MULTI-TENANT ---
            // Menggunakan company_id untuk memisahkan aset antar perusahaan/UMKM
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('perusahaan')->onDelete('cascade');
            
            $table->string('kode_aset'); // Dibuat unik secara logis nanti melalui index composite
            $table->string('nama_aset');
            
            // Relasi ke Kategori Aset
            $table->unsignedBigInteger('kategori_aset_id')->nullable();
            $table->foreign('kategori_aset_id')->references('id')->on('kategori_asets')->onDelete('set null');

            // --- AKUNTANSI ASET ---
            /**
             * PERBAIKAN UTAMA:
             * Tabel akun Anda bernama 'accounts', bukan 'coas'.
             */
            $table->unsignedBigInteger('coa_id')->nullable();
            $table->foreign('coa_id')->references('id')->on('accounts')->onDelete('set null');

            $table->decimal('harga_perolehan', 15, 2)->default(0);
            $table->decimal('biaya_perolehan', 15, 2)->default(0);
            $table->decimal('nilai_residu', 15, 2)->default(0);
            $table->integer('umur_manfaat')->default(5); // Dalam tahun
            $table->decimal('penyusutan_per_tahun', 15, 2)->default(0);
            $table->decimal('penyusutan_per_bulan', 15, 2)->default(0);
            $table->decimal('nilai_buku', 15, 2)->default(0);
            $table->decimal('akumulasi_penyusutan', 15, 2)->default(0);
            $table->decimal('persentase_penyusutan', 5, 2)->nullable();
            
            // --- TANGGAL & STATUS ---
            $table->date('tanggal_perolehan')->nullable();
            $table->date('tanggal_beli')->nullable();
            $table->date('tanggal_akuisisi')->nullable();
            $table->enum('status', ['aktif', 'disewakan', 'dioperasikan', 'dihapus'])->default('aktif');
            $table->enum('metode_penyusutan', ['garis_lurus', 'saldo_menurun', 'sum_of_years_digits'])->default('garis_lurus');
            
            // --- DETAIL TAMBAHAN ---
            $table->string('lokasi')->nullable();
            $table->string('nomor_serial')->nullable();
            $table->text('keterangan')->nullable();
            
            // Log Perubahan (Audit Trail)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            // --- INDEXING & UNIQUE ---
            /**
             * Multi-tenant Unique Constraint:
             * Kode aset harus unik di dalam SATU perusahaan yang sama.
             */
            $table->unique(['company_id', 'kode_aset'], 'unique_aset_per_company');
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asets');
    }
};
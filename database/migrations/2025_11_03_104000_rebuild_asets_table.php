<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tabel lama jika ada
        Schema::dropIfExists('asets');
        
        // Buat tabel baru dengan struktur lengkap
        Schema::create('asets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->unique();
            $table->string('nama_aset');
            $table->unsignedBigInteger('kategori_aset_id')->nullable();
            $table->decimal('harga_perolehan', 15, 2)->default(0);
            $table->decimal('biaya_perolehan', 15, 2)->default(0);
            $table->decimal('nilai_residu', 15, 2)->default(0);
            $table->integer('umur_manfaat')->default(5);
            $table->decimal('penyusutan_per_tahun', 15, 2)->default(0);
            $table->decimal('penyusutan_per_bulan', 15, 2)->default(0);
            $table->decimal('nilai_buku', 15, 2)->default(0);
            $table->decimal('akumulasi_penyusutan', 15, 2)->default(0);
            $table->date('tanggal_beli')->nullable();
            $table->date('tanggal_akuisisi')->nullable();
            $table->enum('status', ['aktif', 'disewakan', 'dioperasikan', 'dihapus'])->default('aktif');
            $table->enum('metode_penyusutan', ['garis_lurus', 'saldo_menurun', 'sum_of_years_digits'])->default('garis_lurus');
            $table->text('keterangan')->nullable();
            
            // Kolom tambahan dari migration lama
            $table->string('kategori')->nullable(); // untuk backward compatibility
            $table->foreignId('coa_id')->nullable()->constrained('coas')->onDelete('set null');
            $table->date('tanggal_perolehan')->nullable();
            $table->decimal('nilai_sisa', 15, 2)->default(0);
            $table->integer('umur_ekonomis_tahun')->nullable();
            $table->decimal('persentase_penyusutan', 5, 2)->nullable();
            $table->string('lokasi')->nullable();
            $table->string('nomor_serial')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asets');
    }
};

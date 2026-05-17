<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk tabel pegawais.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pegawais')) {
            Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            
            // 🔒 Multi-tenant isolation: Memastikan data antar UMKM tidak bercampur
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); 

            // Identitas Pegawai
            $table->string('nomor_induk_pegawai')->nullable(); 
            $table->string('kode_pegawai')->unique();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('no_telepon');
            $table->text('alamat');
            $table->enum('jenis_kelamin', ['L', 'P']);
            
            // Jabatan & Status Administrasi
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans')->onDelete('set null');
            $table->string('jabatan'); 
            $table->date('tanggal_masuk')->nullable();
            $table->enum('status_pegawai', ['aktif', 'non-aktif'])->default('aktif');
            
            // Logika SIMACOST (Sistem Manufaktur Proses Costing)
            $table->enum('kategori_tenaga_kerja', ['BTKL', 'BTKTL'])->default('BTKL');
            $table->string('jenis_pegawai'); 
            
            // Keuangan & Payroll Lengkap (Menyatukan semua field untuk menghindari error 1054)
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tarif_per_jam', 15, 2)->nullable();
            $table->decimal('tarif_per_produk', 15, 2)->default(0); 
            $table->decimal('tarif_lembur', 15, 2)->default(0); // Field yang menyebabkan error tadi sudah aman di sini
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->decimal('asuransi', 15, 2)->default(0);
            
            // Informasi Perbankan
            $table->string('bank')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_rekening')->nullable();
            
            $table->timestamps();
            });
        }
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void 
    {
        Schema::dropIfExists('pegawais');
    }
};
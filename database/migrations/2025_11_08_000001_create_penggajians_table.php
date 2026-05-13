<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Jalankan migrasi untuk tabel penggajians.
     */
    public function up(): void
    {
        Schema::create('penggajians', function (Blueprint $table) {
            $table->id();

            // 🔒 Multi-tenant isolation
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            // Relasi ke Pegawai
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->onDelete('cascade');

            // Data Periode
            $table->string('bulan', 20); 
            $table->string('tahun', 4);  
            $table->date('tanggal_penggajian');

            // Logika Keuangan Dasar
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            
            // Kolom Utama Tunjangan
            $table->decimal('total_tunjangan', 15, 2)->default(0);
            
            // --- DETAIL TUNJANGAN (PENTING: Masukkan di sini agar tidak error 'after tunjangan') ---
            $table->decimal('tunjangan_jabatan', 15, 2)->default(0);
            $table->decimal('tunjangan_transport', 15, 2)->default(0);
            $table->decimal('tunjangan_makan', 15, 2)->default(0);
            // -------------------------------------------------------------------------------------

            $table->decimal('asuransi', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('lembur', 15, 2)->default(0);
            $table->decimal('potongan', 15, 2)->default(0);

            // Logika SIMACOST (Sistem Manufaktur Proses Costing)
            // Fokus pada hasil produksi sesuai kebutuhan Manufaktur
            $table->integer('total_produk_dihasilkan')->default(0); 
            $table->decimal('total_upah_produk', 15, 2)->default(0); 

            // Hasil Akhir: (Gaji Pokok + Upah Produk + Total Tunjangan + Bonus + Lembur) - (Potongan + Asuransi)
            $table->decimal('total_gaji', 15, 2)->default(0); 

            $table->enum('status_bayar', ['pending', 'dibayar'])->default('pending');
            $table->text('catatan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajians');
    }
};
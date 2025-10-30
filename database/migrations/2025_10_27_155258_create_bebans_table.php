<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * Tabel ini digunakan untuk mencatat seluruh jenis beban operasional perusahaan,
     * termasuk beban gaji, listrik, air, sewa, dan lainnya.
     */
    public function up(): void
    {
        Schema::create('bebans', function (Blueprint $table) {
            $table->id();

            // Nama akun beban (misalnya: "Beban Gaji", "Beban Listrik", "Beban Sewa")
            $table->string('nama_akun');

            // Nominal rupiah dari beban
            $table->decimal('nominal', 15, 2);

            // Tanggal transaksi atau pencatatan beban
            $table->date('tanggal');

            // Jenis beban (opsional, untuk pengelompokan tambahan)
            $table->enum('kategori', ['Gaji', 'Operasional', 'Produksi', 'Lainnya'])->default('Lainnya');

            // Keterangan tambahan (opsional)
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('bebans');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Master data untuk proses produksi (Menggoreng, Membumbui, Mengemas, dll)
     * Setiap proses memiliki tarif BTKL (Biaya Tenaga Kerja Langsung) sendiri
     */
    public function up(): void
    {
        Schema::create('proses_produksis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_proses', 20)->unique()->comment('Kode unik proses (PRO-001)');
            $table->string('nama_proses', 100)->comment('Nama proses (Menggoreng, Membumbui, Mengemas)');
            $table->text('deskripsi')->nullable()->comment('Deskripsi proses');
            $table->decimal('tarif_btkl', 15, 2)->default(0)->comment('Tarif BTKL per satuan waktu (Rp/jam atau Rp/unit)');
            $table->string('satuan_btkl', 20)->default('jam')->comment('Satuan waktu (jam, menit, unit)');
            $table->boolean('is_active')->default(true)->comment('Status aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proses_produksis');
    }
};

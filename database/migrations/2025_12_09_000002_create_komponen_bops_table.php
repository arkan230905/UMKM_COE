<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Master data untuk komponen BOP (Biaya Overhead Pabrik)
     * Contoh: Listrik, Gas, Air, Penyusutan Mesin
     */
    public function up(): void
    {
        Schema::create('komponen_bops', function (Blueprint $table) {
            $table->id();
            $table->string('kode_komponen', 20)->unique()->comment('Kode unik komponen (BOP-001)');
            $table->string('nama_komponen', 100)->comment('Nama komponen (Listrik, Gas, Penyusutan Mesin)');
            $table->string('satuan', 20)->comment('Satuan (kWh, m³, jam)');
            $table->decimal('tarif_per_satuan', 15, 2)->default(0)->comment('Tarif per satuan');
            $table->boolean('is_active')->default(true)->comment('Status aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komponen_bops');
    }
};

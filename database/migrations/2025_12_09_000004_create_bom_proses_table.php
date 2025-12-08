<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan proses produksi dalam BOM
     * Setiap BOM bisa memiliki beberapa proses (Menggoreng, Membumbui, Mengemas)
     */
    public function up(): void
    {
        Schema::create('bom_proses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->onDelete('cascade');
            $table->foreignId('proses_produksi_id')->constrained('proses_produksis')->onDelete('restrict');
            $table->integer('urutan')->default(1)->comment('Urutan proses (1, 2, 3...)');
            $table->decimal('durasi', 15, 4)->default(0)->comment('Durasi/kuantitas proses');
            $table->string('satuan_durasi', 20)->default('jam')->comment('Satuan (jam, menit, unit)');
            $table->decimal('biaya_btkl', 15, 2)->default(0)->comment('Calculated: durasi × tarif_btkl');
            $table->decimal('biaya_bop', 15, 2)->default(0)->comment('Calculated: sum of BOP components');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_proses');
    }
};

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
        Schema::create('bop_lainnya', function (Blueprint $table) {
    $table->id();
    $table->string('kode_akun');
    $table->decimal('budget', 15, 2);
    $table->integer('kuantitas_per_jam');
    $table->string('periode');
    $table->string('nama_akun');
    $table->string('metode_pembebanan');
    $table->decimal('aktual', 15, 2)->default(0);
    $table->boolean('is_active')->default(true);
    $table->text('keterangan')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bop_lainnya');
    }
};

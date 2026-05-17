<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->string('nama_bank')->nullable()->comment('Nama bank (BCA, BNI, BRI, Mandiri, dll)');
            $table->string('nomor_rekening')->nullable()->comment('Nomor rekening perusahaan');
            $table->string('nama_pemilik_rekening')->nullable()->comment('Nama pemilik rekening');
        });
    }

    public function down(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'nomor_rekening', 'nama_pemilik_rekening']);
        });
    }
};

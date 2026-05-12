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
        Schema::create('presensi_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presensi_user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->string('status_masuk')->default('hadir'); // hadir, terlambat, izin, sakit
            $table->string('status_keluar')->default('hadir'); // hadir, pulang_cepat, izin, sakit
            $table->text('keterangan')->nullable();
            $table->string('latitude_masuk')->nullable();
            $table->string('longitude_masuk')->nullable();
            $table->string('latitude_keluar')->nullable();
            $table->string('longitude_keluar')->nullable();
            $table->timestamps();
            
            $table->unique(['presensi_user_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_records');
    }
};

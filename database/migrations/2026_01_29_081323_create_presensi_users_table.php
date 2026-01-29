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
        Schema::create('presensi_users', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nik')->unique();
            $table->string('jabatan');
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('kode_perusahaan');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_users');
    }
};

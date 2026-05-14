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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Tambahkan kolom role agar tidak error saat seeding/login
            $table->string('role')->default('owner'); 
            
            /**
             * Kolom relasi ke tabel perusahaan.
             * Menggunakan nama 'perusahaan_id' agar konsisten dengan tabel lainnya di SIMACOST.
             */
            $table->unsignedBigInteger('perusahaan_id')->nullable();
            
            $table->rememberToken();
            $table->timestamps();

            // Index untuk mempercepat pencarian data berdasarkan owner/perusahaan
            $table->index('perusahaan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
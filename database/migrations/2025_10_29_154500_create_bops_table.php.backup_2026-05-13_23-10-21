<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bops')) {
            Schema::create('bops', function (Blueprint $table) {
                $table->id();
                $table->string('kode_akun');
                $table->string('nama_akun');
                $table->decimal('budget', 15, 2)->default(0);
                $table->text('keterangan')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Index untuk pencarian yang lebih cepat
                $table->index('kode_akun');
                $table->index('is_active');
            });
        }
        
        // Tambahkan foreign key constraint jika diperlukan
        // Pastikan tabel coa sudah ada sebelum menambahkan foreign key
        if (Schema::hasTable('coas')) {
            Schema::table('bops', function (Blueprint $table) {
                $table->foreign('kode_akun')
                      ->references('kode_akun')
                      ->on('coas')
                      ->onDelete('restrict')
                      ->onUpdate('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('bops');
    }
};

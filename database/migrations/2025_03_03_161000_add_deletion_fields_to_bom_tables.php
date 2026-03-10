<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bom_details', function (Blueprint $table) {
            $table->string('nama_bahan_terhapus')->nullable();
            $table->decimal('harga_terakhir', 15, 2)->nullable();
            $table->string('satuan_terakhir')->nullable();
            $table->text('catatan_hapus')->nullable();
        });
        
        Schema::table('bom_job_bbb', function (Blueprint $table) {
            $table->string('nama_bahan_terhapus')->nullable();
            $table->decimal('harga_terakhir', 15, 2)->nullable();
            $table->string('satuan_terakhir')->nullable();
            $table->text('catatan_hapus')->nullable();
        });
        
        Schema::table('bom_job_bahan_pendukung', function (Blueprint $table) {
            $table->string('nama_bahan_terhapus')->nullable();
            $table->decimal('harga_terakhir', 15, 2)->nullable();
            $table->string('satuan_terakhir')->nullable();
            $table->text('catatan_hapus')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('bom_details', function (Blueprint $table) {
            $table->dropColumn(['nama_bahan_terhapus', 'harga_terakhir', 'satuan_terakhir', 'catatan_hapus']);
        });
        
        Schema::table('bom_job_bbb', function (Blueprint $table) {
            $table->dropColumn(['nama_bahan_terhapus', 'harga_terakhir', 'satuan_terakhir', 'catatan_hapus']);
        });
        
        Schema::table('bom_job_bahan_pendukung', function (Blueprint $table) {
            $table->dropColumn(['nama_bahan_terhapus', 'harga_terakhir', 'satuan_terakhir', 'catatan_hapus']);
        });
    }
};

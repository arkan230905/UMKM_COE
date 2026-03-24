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
        if (Schema::hasTable('bom_details')) {
            Schema::table('bom_details', function (Blueprint $table) {
                $table->string('nama_bahan_terhapus')->nullable();
                $table->decimal('harga_terakhir', 15, 2)->nullable();
                $table->string('satuan_terakhir')->nullable();
                $table->text('catatan_hapus')->nullable();
            });
        }
        
        if (Schema::hasTable('bom_job_bbb')) {
            Schema::table('bom_job_bbb', function (Blueprint $table) {
                $table->string('nama_bahan_terhapus')->nullable();
                $table->decimal('harga_terakhir', 15, 2)->nullable();
                $table->string('satuan_terakhir')->nullable();
                $table->text('catatan_hapus')->nullable();
            });
        }
        
        if (Schema::hasTable('bom_job_bahan_pendukung')) {
            Schema::table('bom_job_bahan_pendukung', function (Blueprint $table) {
                $table->string('nama_bahan_terhapus')->nullable();
                $table->decimal('harga_terakhir', 15, 2)->nullable();
                $table->string('satuan_terakhir')->nullable();
                $table->text('catatan_hapus')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('bom_details')) {
            Schema::table('bom_details', function (Blueprint $table) {
                $table->dropColumn(['nama_bahan_terhapus', 'harga_terakhir', 'satuan_terakhir', 'catatan_hapus']);
            });
        }
        
        if (Schema::hasTable('bom_job_bbb')) {
            Schema::table('bom_job_bbb', function (Blueprint $table) {
                $table->dropColumn(['nama_bahan_terhapus', 'harga_terakhir', 'satuan_terakhir', 'catatan_hapus']);
            });
        }
        
        if (Schema::hasTable('bom_job_bahan_pendukung')) {
            Schema::table('bom_job_bahan_pendukung', function (Blueprint $table) {
                $table->dropColumn(['nama_bahan_terhapus', 'harga_terakhir', 'satuan_terakhir', 'catatan_hapus']);
            });
        }
    }
};

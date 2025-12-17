<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip migration - bops table sudah ada dengan struktur yang tepat
        return;
    }

    public function down()
    {
        // Recreate the bops table if needed
        if (!Schema::hasTable('bops')) {
            Schema::create('bops', function (Blueprint $table) {
                $table->id();
                $table->string('kode_akun', 10);
                $table->string('nama_biaya');
                $table->decimal('jumlah', 15, 2);
                $table->string('periode');
                $table->timestamps();

                $table->foreign('kode_akun')
                    ->references('kode_akun')
                    ->on('coas')
                    ->onDelete('cascade');
            });
        }
    }
};

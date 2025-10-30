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
                $table->string('keterangan')->nullable();
                $table->decimal('nominal', 15, 2)->default(0);
                $table->date('tanggal')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('bops');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop foreign key constraints first
        Schema::table('bops', function (Blueprint $table) {
            if (Schema::hasColumn('bops', 'kode_akun')) {
                $table->dropForeign(['kode_akun']);
            }
        });

        // Now drop the table
        Schema::dropIfExists('bops');
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

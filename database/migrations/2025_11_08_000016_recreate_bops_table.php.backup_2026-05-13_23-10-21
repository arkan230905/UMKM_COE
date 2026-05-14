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
                $table->string('kode_akun', 20);
                $table->string('nama_akun', 100);
                $table->decimal('budget', 15, 2)->default(0);
                $table->decimal('sisa_budget', 15, 2)->default(0);
                $table->text('keterangan')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                // Add foreign key constraint
                $table->foreign('kode_akun')
                    ->references('kode_akun')
                    ->on('coas')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('bops');
    }
};

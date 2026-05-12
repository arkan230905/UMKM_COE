<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('bops')) {
            Schema::create('bops', function (Blueprint $table) {
                $table->id();
                $table->string('kode')->nullable();
                $table->string('kode_akun')->nullable();
                $table->string('nama_akun')->nullable();
                $table->string('nama')->nullable();
                $table->string('kategori')->nullable();
                $table->decimal('jumlah', 18, 4)->nullable();
                $table->decimal('harga_satuan', 18, 2)->nullable();
                $table->decimal('total', 18, 2)->nullable();
                $table->string('keterangan')->nullable();
                $table->date('tanggal')->nullable();
                $table->unsignedBigInteger('coa_id')->nullable();
                $table->decimal('budget', 18, 2)->default(0);
                $table->string('periode', 7)->nullable();
                $table->boolean('is_active')->default(true);
                $table->decimal('nominal', 18, 2)->default(0);
                $table->timestamps();

                $table->index(['coa_id']);
                $table->index(['periode']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bops')) {
            Schema::drop('bops');
        }
    }
};

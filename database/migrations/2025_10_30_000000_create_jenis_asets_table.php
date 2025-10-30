<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_asets', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // Insert default data
        DB::table('jenis_asets')->insert([
            ['nama' => 'Aset Tetap', 'deskripsi' => 'Aset berwujud yang digunakan dalam operasional perusahaan dan memiliki masa manfaat lebih dari satu tahun'],
            ['nama' => 'Aset Tidak Tetap', 'deskripsi' => 'Aset yang digunakan habis dalam satu siklus operasi normal perusahaan'],
            ['nama' => 'Aset Tidak Berwujud', 'deskripsi' => 'Aset non-fisik yang memiliki nilai ekonomi'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_asets');
    }
};

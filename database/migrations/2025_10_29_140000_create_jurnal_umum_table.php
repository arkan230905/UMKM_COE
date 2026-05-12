<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jurnal_umum', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coa_id')->constrained('coas')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('keterangan');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->string('referensi')->nullable();
            $table->string('tipe_referensi')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jurnal_umum');
    }
};

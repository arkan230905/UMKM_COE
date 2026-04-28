<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ongkir_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('jarak_min', 8, 2)->default(0)->comment('dalam km');
            $table->decimal('jarak_max', 8, 2)->nullable()->comment('dalam km, null = tidak terbatas');
            $table->decimal('harga_ongkir', 15, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->boolean('status')->default(true)->comment('true = aktif, false = nonaktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ongkir_settings');
    }
};

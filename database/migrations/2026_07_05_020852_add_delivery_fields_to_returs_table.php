<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            $table->string('metode_pengambilan_retur')->nullable();
            $table->text('alamat_retur')->nullable();
            $table->text('detail_alamat_retur')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->decimal('ongkir_retur', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            $table->dropColumn([
                'metode_pengambilan_retur',
                'alamat_retur',
                'detail_alamat_retur',
                'kecamatan',
                'kota',
                'provinsi',
                'kode_pos',
                'latitude',
                'longitude',
                'ongkir_retur'
            ]);
        });
    }
};

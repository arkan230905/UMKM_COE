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
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropUnique('pegawais_kode_pegawai_unique');
            $table->unique(['user_id', 'kode_pegawai'], 'pegawais_user_id_kode_pegawai_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropUnique('pegawais_user_id_kode_pegawai_unique');
            $table->unique('kode_pegawai', 'pegawais_kode_pegawai_unique');
        });
    }
};

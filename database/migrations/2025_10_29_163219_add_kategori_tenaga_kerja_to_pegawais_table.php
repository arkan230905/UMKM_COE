<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'kategori_tenaga_kerja')) {
                $table->enum('kategori_tenaga_kerja', ['BTKL', 'BTKTL'])->default('BTKL')->after('jabatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn('kategori_tenaga_kerja');
        });
    }
};

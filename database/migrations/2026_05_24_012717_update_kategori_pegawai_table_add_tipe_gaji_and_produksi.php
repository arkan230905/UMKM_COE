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
        Schema::table('kategori_pegawai', function (Blueprint $table) {
            if (!Schema::hasColumn('kategori_pegawai', 'tipe_gaji')) {
                $table->enum('tipe_gaji', ['btkl', 'btkti'])->default('btkti')->after('deskripsi');
            }
            if (!Schema::hasColumn('kategori_pegawai', 'is_produksi')) {
                $table->boolean('is_produksi')->default(false)->after('tipe_gaji');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_pegawai', function (Blueprint $table) {
            $table->dropColumn(['tipe_gaji', 'is_produksi']);
        });
    }
};

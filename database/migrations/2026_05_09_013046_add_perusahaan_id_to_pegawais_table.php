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
            // Tambah kolom perusahaan_id jika belum ada
            if (!Schema::hasColumn('pegawais', 'perusahaan_id')) {
                $table->unsignedBigInteger('perusahaan_id')->nullable()->after('user_id');
                $table->foreign('perusahaan_id')->references('id')->on('perusahaan')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'perusahaan_id')) {
                $table->dropForeign(['perusahaan_id']);
                $table->dropColumn('perusahaan_id');
            }
        });
    }
};

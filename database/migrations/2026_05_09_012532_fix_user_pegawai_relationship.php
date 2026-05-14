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
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom pegawai_id jika belum ada
            if (!Schema::hasColumn('users', 'pegawai_id')) {
                $table->unsignedBigInteger('pegawai_id')->nullable()->after('id');
                $table->foreign('pegawai_id')->references('id')->on('pegawais')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pegawai_id')) {
                $table->dropForeign(['pegawai_id']);
                $table->dropColumn('pegawai_id');
            }
        });
    }
};

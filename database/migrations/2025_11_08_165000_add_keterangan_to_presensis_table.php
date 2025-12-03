<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Periksa apakah kolom sudah ada sebelum menambahkannya
        if (!Schema::hasColumn('presensis', 'keterangan')) {
            Schema::table('presensis', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('status');
            });
        }
    }

    public function down()
    {
        // Jangan hapus kolom untuk mencegah kehilangan data
        // Jika diperlukan rollback, buat migrasi terpisah
    }
};

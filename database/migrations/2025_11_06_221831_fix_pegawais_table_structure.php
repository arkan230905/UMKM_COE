<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip migration - tabel pegawais sudah ada dengan struktur yang tepat
        if (!Schema::hasTable('pegawais')) {
            return;
        }

        // Jika tabel sudah memiliki kolom id, skip
        if (Schema::hasColumn('pegawais', 'id')) {
            return;
        }
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->dropUnique(['kode_pegawai']);
            }
        });
    }
};

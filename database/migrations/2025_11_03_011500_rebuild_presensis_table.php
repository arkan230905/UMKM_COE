<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip migration - tabel presensis sudah ada dengan struktur yang tepat
        if (!Schema::hasTable('presensis')) {
            return;
        }

        // Jika tabel sudah memiliki kolom pegawai_id, skip rebuild
        if (Schema::hasColumn('presensis', 'pegawai_id')) {
            return;
        }
    }

    public function down(): void
    {
        // Tidak melakukan rollback penuh karena kompleks; hanya berhenti jika ada.
        if (Schema::hasTable('presensis_new')) {
            Schema::drop('presensis_new');
        }
    }
};

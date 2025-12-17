<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asets')) return;

        // Skip - kolom sudah ditambahkan di migration sebelumnya
        return;
    }

    public function down(): void
    {
        // SQLite tidak mendukung DROP COLUMN dengan mudah; skip rollback
    }
};

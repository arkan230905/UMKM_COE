<?php

use Illuminate\Database\Migrations\Migration;

// FIXED: Migration ini sebelumnya mengupdate data hardcoded (bahan_baku_id=1)
// yang akan GAGAL di fresh install karena data belum ada.
// Data ini seharusnya ada di seeder, bukan migration.
return new class extends Migration
{
    public function up(): void
    {
        // No-op: data update removed, use seeder instead
    }

    public function down(): void
    {
        // No-op
    }
};

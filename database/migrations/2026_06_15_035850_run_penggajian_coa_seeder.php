<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan seeder otomatis saat migrasi berjalan di Jenkins
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\UpdatePenggajianCoasSeeder',
            '--force' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada down migration
    }
};

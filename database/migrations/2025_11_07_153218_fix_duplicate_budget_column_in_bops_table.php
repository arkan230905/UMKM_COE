<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek apakah tabel bops ada
        if (Schema::hasTable('bops')) {
            // Hapus kolom budget jika sudah ada
            if (Schema::hasColumn('bops', 'budget')) {
                // Gunakan DB::statement untuk SQLite
                DB::statement('ALTER TABLE bops DROP COLUMN budget');
            }
            
            // Tambahkan kembali kolom budget
            Schema::table('bops', function (Blueprint $table) {
                $table->decimal('budget', 15, 2)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bops') && Schema::hasColumn('bops', 'budget')) {
            Schema::table('bops', function (Blueprint $table) {
                $table->dropColumn('budget');
            });
        }
    }
};

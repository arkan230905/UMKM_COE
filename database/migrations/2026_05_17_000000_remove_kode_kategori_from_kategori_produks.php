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
        Schema::table('kategori_produks', function (Blueprint $table) {
            // Check if the column exists before trying to drop it
            if (Schema::hasColumn('kategori_produks', 'kode_kategori')) {
                // Drop the unique constraint first (if it exists)
                try {
                    $table->dropUnique(['kode_kategori']);
                } catch (\Exception $e) {
                    // Ignore if the unique constraint doesn't exist
                }
                // Then drop the column
                $table->dropColumn('kode_kategori');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_produks', function (Blueprint $table) {
            // Add the column back if migration is rolled back
            $table->string('kode_kategori', 20)->unique()->after('user_id');
        });
    }
};

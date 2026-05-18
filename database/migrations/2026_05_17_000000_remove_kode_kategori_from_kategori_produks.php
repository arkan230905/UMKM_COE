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
            // Drop the unique constraint first
            $table->dropUnique(['kode_kategori']);
            // Then drop the column
            $table->dropColumn('kode_kategori');
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

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
        Schema::table('bahan_bakus', function (Blueprint $table) {
            if (!Schema::hasColumn('bahan_bakus', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('stok_minimum');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            if (Schema::hasColumn('bahan_bakus', 'deskripsi')) {
                $table->dropColumn('deskripsi');
            }
        });
    }
};

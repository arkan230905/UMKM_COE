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
        // Add stok column to bahan_bakus table
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->decimal('stok', 15, 4)->default(0)->after('stok_minimum');
        });

        // Add stok column to bahan_pendukungs table
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            $table->decimal('stok', 15, 4)->default(0)->after('stok_minimum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropColumn('stok');
        });

        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            $table->dropColumn('stok');
        });
    }
};

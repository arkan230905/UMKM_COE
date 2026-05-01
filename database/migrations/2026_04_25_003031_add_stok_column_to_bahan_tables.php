<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add stok column to bahan_bakus if not exists
        if (Schema::hasTable('bahan_bakus') && !Schema::hasColumn('bahan_bakus', 'stok')) {
            Schema::table('bahan_bakus', function (Blueprint $table) {
                $table->decimal('stok', 15, 4)->default(0)->after('stok_minimum');
            });
        }

        // Add stok column to bahan_pendukungs if not exists
        if (Schema::hasTable('bahan_pendukungs') && !Schema::hasColumn('bahan_pendukungs', 'stok')) {
            Schema::table('bahan_pendukungs', function (Blueprint $table) {
                $table->decimal('stok', 15, 4)->default(0)->after('stok_minimum');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bahan_bakus') && Schema::hasColumn('bahan_bakus', 'stok')) {
            Schema::table('bahan_bakus', function (Blueprint $table) {
                $table->dropColumn('stok');
            });
        }
        if (Schema::hasTable('bahan_pendukungs') && Schema::hasColumn('bahan_pendukungs', 'stok')) {
            Schema::table('bahan_pendukungs', function (Blueprint $table) {
                $table->dropColumn('stok');
            });
        }
    }
};

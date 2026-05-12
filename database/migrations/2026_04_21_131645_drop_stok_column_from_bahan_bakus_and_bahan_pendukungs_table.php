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
        // Drop stok column from bahan_bakus if it exists
        Schema::table('bahan_bakus', function (Blueprint $table) {
            if (Schema::hasColumn('bahan_bakus', 'stok')) {
                $table->dropColumn('stok');
            }
        });
        
        // Drop stok column from bahan_pendukungs if it exists
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            if (Schema::hasColumn('bahan_pendukungs', 'stok')) {
                $table->dropColumn('stok');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add stok column back to bahan_bakus
        Schema::table('bahan_bakus', function (Blueprint $table) {
            if (!Schema::hasColumn('bahan_bakus', 'stok')) {
                $table->decimal('stok', 15, 4)->default(0)->after('harga_rata_rata');
            }
        });
        
        // Add stok column back to bahan_pendukungs
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            if (!Schema::hasColumn('bahan_pendukungs', 'stok')) {
                $table->decimal('stok', 15, 4)->default(0)->after('harga_satuan');
            }
        });
    }
};

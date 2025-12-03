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
        // Drop foreign key constraints that reference coas table
        Schema::table('bops', function (Blueprint $table) {
            if (Schema::hasColumn('bops', 'kode_akun')) {
                // Drop foreign key constraint if it exists
                $table->dropForeign(['kode_akun']);
                // Change the column to match the new data type
                $table->string('kode_akun', 10)->change();
            }
        });

        // Now modify the coas table
        Schema::table('coas', function (Blueprint $table) {
            // Change the column type to match the one in bops table
            $table->string('kode_akun', 10)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way migration, but we'll provide a way to revert
        // by adding back the foreign key constraint
        Schema::table('bops', function (Blueprint $table) {
            if (Schema::hasColumn('bops', 'kode_akun')) {
                // Add the foreign key back
                $table->foreign('kode_akun')
                      ->references('kode_akun')
                      ->on('coas')
                      ->onDelete('cascade');
            }
        });
    }
};

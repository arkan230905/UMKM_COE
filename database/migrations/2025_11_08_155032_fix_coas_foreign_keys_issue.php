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
        // Skip this migration - constraints already handled
        return;
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

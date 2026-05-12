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
        Schema::table('bops', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['kode_akun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bops', function (Blueprint $table) {
            // Re-add foreign key constraint
            $table->foreign('kode_akun')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('cascade');
        });
    }
};

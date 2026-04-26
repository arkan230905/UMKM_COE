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
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->string('nama_bop_proses')->nullable()->after('id');
            
            // Make proses_produksi_id nullable since we're using nama_bop_proses now
            $table->unsignedBigInteger('proses_produksi_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->dropColumn('nama_bop_proses');
        });
    }
};

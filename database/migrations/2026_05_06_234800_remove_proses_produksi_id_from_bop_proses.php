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
            // Drop the column directly (Laravel will handle foreign keys)
            if (Schema::hasColumn('bop_proses', 'proses_produksi_id')) {
                $table->dropColumn('proses_produksi_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->unsignedBigInteger('proses_produksi_id')->nullable()->after('nama_bop_proses');
        });
    }
};

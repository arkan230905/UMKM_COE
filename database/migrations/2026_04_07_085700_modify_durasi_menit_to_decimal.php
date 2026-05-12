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
        Schema::table('produksi_proses', function (Blueprint $table) {
            $table->decimal('durasi_menit', 8, 2)->nullable()->change(); // Change from integer to decimal(8,2)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi_proses', function (Blueprint $table) {
            $table->integer('durasi_menit')->nullable()->change(); // Revert back to integer
        });
    }
};
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
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->unsignedBigInteger('jabatan_id')->nullable()->after('kapasitas_per_jam');
            $table->foreign('jabatan_id')->references('id')->on('jabatans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropColumn('jabatan_id');
        });
    }
};

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
        Schema::table('produksi_details', function (Blueprint $table) {
            $table->unsignedBigInteger('bahan_pendukung_id')->nullable()->after('bahan_baku_id');
            $table->index('bahan_pendukung_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi_details', function (Blueprint $table) {
            $table->dropIndex(['bahan_pendukung_id']);
            $table->dropColumn('bahan_pendukung_id');
        });
    }
};

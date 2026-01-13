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
        Schema::table('bom_proses_bops', function (Blueprint $table) {
            $table->string('nama_bop')->nullable()->after('bop_id')->comment('Nama BOP untuk custom entries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom_proses_bops', function (Blueprint $table) {
            $table->dropColumn('nama_bop');
        });
    }
};

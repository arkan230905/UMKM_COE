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
        Schema::table('penggajians', function (Blueprint $table) {
            // Add nomor_penggajian untuk multi-tenant numbering per user
            $table->string('nomor_penggajian')->nullable()->after('id');
            
            // Add unique constraint per user_id
            $table->unique(['user_id', 'nomor_penggajian']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'nomor_penggajian']);
            $table->dropColumn('nomor_penggajian');
        });
    }
};

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
        Schema::table('jurnal_umum', function (Blueprint $table) {
            $table->string('tipe_referensi')->nullable()->after('kredit');
            $table->string('referensi')->nullable()->after('tipe_referensi');
            $table->unsignedBigInteger('created_by')->nullable()->after('referensi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_umum', function (Blueprint $table) {
            $table->dropColumn(['tipe_referensi', 'referensi', 'created_by']);
        });
    }
};

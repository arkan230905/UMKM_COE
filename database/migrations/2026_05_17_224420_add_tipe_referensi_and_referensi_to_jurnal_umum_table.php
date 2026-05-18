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
            if (!Schema::hasColumn('jurnal_umum', 'tipe_referensi')) {
                $table->string('tipe_referensi')->nullable()->after('kredit');
            }
            if (!Schema::hasColumn('jurnal_umum', 'referensi')) {
                $table->string('referensi')->nullable()->after('tipe_referensi');
            }
            if (!Schema::hasColumn('jurnal_umum', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('referensi');
            }
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

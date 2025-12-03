<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            if (!Schema::hasColumn('penggajians', 'coa_kasbank')) {
                $table->string('coa_kasbank', 10)->default('1101')->after('tanggal_penggajian');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            if (Schema::hasColumn('penggajians', 'coa_kasbank')) {
                $table->dropColumn('coa_kasbank');
            }
        });
    }
};

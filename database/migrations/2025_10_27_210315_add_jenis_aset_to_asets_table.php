<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (!Schema::hasColumn('asets', 'jenis_aset')) {
                $table->string('jenis_aset')->default('Aset Tetap')->after('kategori');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'jenis_aset')) {
                $table->dropColumn('jenis_aset');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualans', 'harga_satuan')) {
                $table->decimal('harga_satuan', 15, 2)->nullable()->after('tanggal');
            }
            if (!Schema::hasColumn('penjualans', 'jumlah')) {
                $table->decimal('jumlah', 15, 4)->default(0)->after('harga_satuan');
            }
            if (!Schema::hasColumn('penjualans', 'diskon_nominal')) {
                $table->decimal('diskon_nominal', 15, 2)->default(0)->after('jumlah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('penjualans', 'diskon_nominal')) {
                $table->dropColumn('diskon_nominal');
            }
            if (Schema::hasColumn('penjualans', 'jumlah')) {
                $table->dropColumn('jumlah');
            }
            if (Schema::hasColumn('penjualans', 'harga_satuan')) {
                $table->dropColumn('harga_satuan');
            }
        });
    }
};

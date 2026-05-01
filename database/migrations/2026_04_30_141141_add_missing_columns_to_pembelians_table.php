<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            if (!Schema::hasColumn('pembelians', 'bukti_faktur')) {
                $table->string('bukti_faktur')->nullable()->after('nomor_faktur');
            }
            if (!Schema::hasColumn('pembelians', 'kode_pembelian')) {
                $table->string('kode_pembelian')->nullable()->after('bukti_faktur');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('pembelians', 'bukti_faktur'))   $cols[] = 'bukti_faktur';
            if (Schema::hasColumn('pembelians', 'kode_pembelian')) $cols[] = 'kode_pembelian';
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};

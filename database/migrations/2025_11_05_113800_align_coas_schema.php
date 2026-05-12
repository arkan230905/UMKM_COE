<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            if (!Schema::hasColumn('coas', 'kategori_akun')) {
                $table->string('kategori_akun')->nullable()->after('nama_akun');
            }
            if (!Schema::hasColumn('coas', 'kode_induk')) {
                $table->string('kode_induk')->nullable()->after('tipe_akun');
            }
            if (!Schema::hasColumn('coas', 'saldo_normal')) {
                $table->decimal('saldo_normal', 15, 2)->default(0)->after('kode_induk');
            }
            if (!Schema::hasColumn('coas', 'saldo_awal')) {
                $table->decimal('saldo_awal', 15, 2)->default(0)->after('saldo_normal');
            }
            if (!Schema::hasColumn('coas', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('saldo_awal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            foreach (['kategori_akun','kode_induk','saldo_normal','saldo_awal','keterangan'] as $col) {
                if (Schema::hasColumn('coas', $col)) {
                    try { $table->dropColumn($col); } catch (\Throwable $e) {}
                }
            }
        });
    }
};

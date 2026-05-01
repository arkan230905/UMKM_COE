<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safely drop FK from bops if exists
        if (Schema::hasTable('bops')) {
            try {
                Schema::table('bops', function (Blueprint $table) {
                    $table->dropForeign('bops_kode_akun_foreign');
                });
            } catch (\Exception $e) {
                // FK may not exist, continue
            }
        }

        // Safely drop FK from coa_period_balances if exists
        if (Schema::hasTable('coa_period_balances')) {
            try {
                Schema::table('coa_period_balances', function (Blueprint $table) {
                    $table->dropForeign('coa_period_balances_kode_akun_foreign');
                });
            } catch (\Exception $e) {
                // FK may not exist, continue
            }
        }

        Schema::table('coas', function (Blueprint $table) {
            $table->string('kode_akun', 10)->change();

            if (!Schema::hasColumn('coas', 'kategori_akun')) {
                $table->string('kategori_akun', 50)->nullable()->after('tipe_akun');
            }
            if (!Schema::hasColumn('coas', 'is_akun_header')) {
                $table->boolean('is_akun_header')->default(false)->after('kategori_akun');
            }
            if (!Schema::hasColumn('coas', 'kode_induk')) {
                $table->string('kode_induk', 10)->nullable()->after('is_akun_header');
            }
            if (!Schema::hasColumn('coas', 'saldo_normal')) {
                $table->string('saldo_normal', 10)->nullable()->after('kode_induk');
            }
            if (!Schema::hasColumn('coas', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('saldo_normal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            try { $table->dropForeign(['kode_induk']); } catch (\Exception $e) {}
            $cols = ['kategori_akun', 'is_akun_header', 'kode_induk', 'saldo_normal', 'keterangan'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('coas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

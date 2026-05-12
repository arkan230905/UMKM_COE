<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            if (!Schema::hasColumn('coas', 'tanggal_saldo_awal')) {
                $table->date('tanggal_saldo_awal')->nullable()->after('saldo_awal');
            }
            if (!Schema::hasColumn('coas', 'posted_saldo_awal')) {
                $table->boolean('posted_saldo_awal')->default(false)->after('tanggal_saldo_awal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            if (Schema::hasColumn('coas', 'posted_saldo_awal')) {
                try { $table->dropColumn('posted_saldo_awal'); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('coas', 'tanggal_saldo_awal')) {
                try { $table->dropColumn('tanggal_saldo_awal'); } catch (\Throwable $e) {}
            }
        });
    }
};

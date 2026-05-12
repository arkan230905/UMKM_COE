<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coa_period_balances', function (Blueprint $table) {
            if (!Schema::hasColumn('coa_period_balances', 'coa_id')) {
                $table->unsignedBigInteger('coa_id')->nullable()->after('id');
                $table->index('coa_id');
            }
        });

        // Backfill coa_id using kode_akun
        try {
            DB::statement(
                "UPDATE coa_period_balances cpb JOIN coas c ON cpb.kode_akun = c.kode_akun SET cpb.coa_id = c.id WHERE cpb.coa_id IS NULL"
            );
        } catch (\Throwable $e) {
            try {
                $rows = DB::table('coa_period_balances')->whereNull('coa_id')->get(['id', 'kode_akun']);
                foreach ($rows as $row) {
                    $coaId = DB::table('coas')->where('kode_akun', $row->kode_akun)->value('id');
                    if ($coaId) {
                        DB::table('coa_period_balances')->where('id', $row->id)->update(['coa_id' => $coaId]);
                    }
                }
            } catch (\Throwable $ignored) {
                // ignore
            }
        }

        Schema::table('coa_period_balances', function (Blueprint $table) {
            if (Schema::hasColumn('coa_period_balances', 'coa_id')) {
                try {
                    $table->foreign('coa_id')->references('id')->on('coas')->onDelete('cascade');
                } catch (\Throwable $e) {
                    // ignore if FK already exists
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('coa_period_balances', function (Blueprint $table) {
            try {
                $table->dropForeign(['coa_id']);
            } catch (\Throwable $e) {
                // ignore
            }

            if (Schema::hasColumn('coa_period_balances', 'coa_id')) {
                try {
                    $table->dropIndex(['coa_id']);
                } catch (\Throwable $e) {
                    // ignore
                }

                try {
                    $table->dropColumn('coa_id');
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        });
    }
};

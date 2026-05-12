<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ensure columns exist in bops
        if (Schema::hasTable('bops')) {
            Schema::table('bops', function (Blueprint $table) {
                if (!Schema::hasColumn('bops', 'nama_akun')) {
                    $table->string('nama_akun')->nullable()->after('kode_akun');
                }
                if (!Schema::hasColumn('bops', 'budget')) {
                    $table->decimal('budget', 18, 2)->default(0)->after('nominal');
                }
                if (!Schema::hasColumn('bops', 'periode')) {
                    $table->string('periode', 7)->nullable()->after('budget'); // YYYY-MM
                }
                if (!Schema::hasColumn('bops', 'keterangan')) {
                    $table->string('keterangan')->nullable()->after('periode');
                }
            });

            // Unique key for (coa_id, periode)
            try {
                Schema::table('bops', function (Blueprint $table) {
                    $table->unique(['coa_id', 'periode'], 'bops_coa_id_periode_unique');
                });
            } catch (\Throwable $e) {
                // ignore if already exists
            }
        }

        // Migrate data from bop_budgets into bops
        if (Schema::hasTable('bop_budgets') && Schema::hasTable('bops')) {
            $rows = DB::table('bop_budgets')->select(
                'coa_id', 'kode_akun', 'nama_akun', 'jumlah_budget', 'periode', 'keterangan'
            )->get();
            foreach ($rows as $r) {
                DB::table('bops')->updateOrInsert(
                    ['coa_id' => $r->coa_id, 'periode' => $r->periode],
                    [
                        'kode_akun' => $r->kode_akun,
                        'nama_akun' => $r->nama_akun,
                        'budget' => $r->jumlah_budget ?? 0,
                        'keterangan' => $r->keterangan,
                        'is_active' => DB::raw('COALESCE(is_active,1)'),
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Revert columns if needed (non-destructive: keep columns)
        try {
            Schema::table('bops', function (Blueprint $table) {
                if (Schema::hasColumn('bops', 'bops_coa_id_periode_unique')) {
                    $table->dropUnique('bops_coa_id_periode_unique');
                }
            });
        } catch (\Throwable $e) {}
        // Keep data as is; no destructive rollback
    }
};

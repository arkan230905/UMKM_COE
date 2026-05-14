<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add coa_id if missing
        Schema::table('bops', function (Blueprint $table) {
            if (!Schema::hasColumn('bops', 'coa_id')) {
                $table->unsignedBigInteger('coa_id')->nullable()->after('id');
            }
        });

        // Backfill coa_id using matching kode_akun between bops and coas
        try {
            // Works on MySQL/MariaDB
            DB::statement(
                "UPDATE bops b JOIN coas c ON b.kode_akun = c.kode_akun SET b.coa_id = c.id WHERE b.coa_id IS NULL"
            );
        } catch (\Throwable $e) {
            // Fallback: iterate if JOIN update not supported (e.g., sqlite)
            try {
                $rows = DB::table('bops')->whereNull('coa_id')->get(['id','kode_akun']);
                foreach ($rows as $row) {
                    $coa = DB::table('coas')->where('kode_akun', $row->kode_akun)->value('id');
                    if ($coa) {
                        DB::table('bops')->where('id', $row->id)->update(['coa_id' => $coa]);
                    }
                }
            } catch (\Throwable $ignored) {}
        }

        // Optional: add FK if columns exist
        Schema::table('bops', function (Blueprint $table) {
            if (Schema::hasColumn('bops', 'coa_id') && Schema::hasColumn('coas', 'id')) {
                try {
                    $table->foreign('coa_id')->references('id')->on('coas')->onDelete('cascade');
                } catch (\Throwable $e) {
                    // ignore if FK already exists or cannot be added
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('bops', function (Blueprint $table) {
            try { $table->dropForeign(['coa_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('bops', 'coa_id')) {
                try { $table->dropColumn('coa_id'); } catch (\Throwable $e) {}
            }
        });
    }
};

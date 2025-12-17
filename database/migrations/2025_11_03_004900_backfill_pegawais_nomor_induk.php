<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pegawais') || !Schema::hasColumn('pegawais', 'nomor_induk_pegawai')) {
            Log::warning('backfill_pegawais_nomor_induk: skipping, column pegawais.nomor_induk_pegawai not found');
            return;
        }

        // Skip jika kolom id tidak ada
        if (!Schema::hasColumn('pegawais', 'id')) {
            Log::warning('backfill_pegawais_nomor_induk: skipping, column id not found');
            return;
        }

        try {
            // Backfill nomor_induk_pegawai for existing rows that are NULL/empty
            $rows = DB::table('pegawais')
                ->whereNull('nomor_induk_pegawai')
                ->orWhere('nomor_induk_pegawai', '=','')
                ->orderBy('id')
                ->get(['id']);

            // Determine starting counter from existing max
            $counter = 1;
            $last = DB::table('pegawais')
                ->whereNotNull('nomor_induk_pegawai')
                ->where('nomor_induk_pegawai','!=','')
                ->orderBy('nomor_induk_pegawai','desc')
                ->value('nomor_induk_pegawai');
            if ($last && preg_match('/EMP(\d{4})/', $last, $m)) {
                $counter = intval($m[1]) + 1;
            }

            foreach ($rows as $r) {
                $nip = 'EMP' . str_pad($counter, 4, '0', STR_PAD_LEFT);
                DB::table('pegawais')->where('id', $r->id)->update(['nomor_induk_pegawai' => $nip]);
                $counter++;
            }
        } catch (\Exception $e) {
            Log::warning('backfill_pegawais_nomor_induk: error - ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        // No-op
    }
};

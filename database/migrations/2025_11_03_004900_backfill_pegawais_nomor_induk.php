<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill nomor_induk_pegawai for existing rows that are NULL/empty
        $rows = DB::table('pegawais')->whereNull('nomor_induk_pegawai')->orWhere('nomor_induk_pegawai','')->orderBy('id')->get(['id','nomor_induk_pegawai']);
        $counter = 1;
        foreach ($rows as $r) {
            // Find max existing EMP number
            $last = DB::table('pegawais')->whereNotNull('nomor_induk_pegawai')->where('nomor_induk_pegawai','!=','')->orderBy('nomor_induk_pegawai','desc')->value('nomor_induk_pegawai');
            if ($last && preg_match('/EMP(\d{4})/', $last, $m)) {
                $counter = max($counter, intval($m[1]) + 1);
            }
            $nip = 'EMP' . str_pad($counter, 4, '0', STR_PAD_LEFT);
            DB::table('pegawais')->where('id', $r->id)->update(['nomor_induk_pegawai' => $nip]);
            $counter++;
        }
    }

    public function down(): void
    {
        // No-op
    }
};

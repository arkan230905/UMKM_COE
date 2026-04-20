<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateToModernJournal extends Command
{
    protected $signature = 'migrate:to-modern-journal';
    protected $description = 'Migrate penggajian and pembayaran beban to modern journal system';

    public function handle()
    {
        $this->info('=== MIGRATING PENGGAJIAN ===');

        $penggajianList = DB::table('penggajians')
            ->leftJoin('pegawais', 'penggajians.pegawai_id', '=', 'pegawais.id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('journal_entries')
                    ->whereColumn('journal_entries.ref_id', 'penggajians.id')
                    ->where('journal_entries.ref_type', 'penggajian');
            })
            ->select('penggajians.id', 'penggajians.tanggal_penggajian', 'penggajians.total_gaji', 'penggajians.coa_kasbank', 'pegawais.nama')
            ->get();

        $this->info("Found " . count($penggajianList) . " penggajian records to migrate");

        foreach ($penggajianList as $penggajian) {
            $journalEntryId = DB::table('journal_entries')->insertGetId([
                'tanggal' => $penggajian->tanggal_penggajian,
                'ref_type' => 'penggajian',
                'ref_id' => $penggajian->id,
                'memo' => 'Penggajian ' . $penggajian->nama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $coaBebanId = DB::table('coas')->where('kode_akun', '52')->value('id');
            if (!$coaBebanId) {
                $coaBebanId = DB::table('coas')->where('kode_akun', '54')->value('id');
            }

            $coaKasId = DB::table('coas')->where('kode_akun', $penggajian->coa_kasbank)->value('id');
            if (!$coaKasId) {
                $coaKasId = DB::table('coas')->where('kode_akun', '111')->value('id');
            }

            // DEBIT
            DB::table('journal_lines')->insert([
                'journal_entry_id' => $journalEntryId,
                'coa_id' => $coaBebanId,
                'debit' => $penggajian->total_gaji,
                'credit' => 0,
                'memo' => 'Beban Gaji ' . $penggajian->nama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // CREDIT
            DB::table('journal_lines')->insert([
                'journal_entry_id' => $journalEntryId,
                'coa_id' => $coaKasId,
                'debit' => 0,
                'credit' => $penggajian->total_gaji,
                'memo' => 'Pembayaran Gaji ' . $penggajian->nama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("✅ Migrated penggajian ID: " . $penggajian->id);
        }

        $this->info("\n=== MIGRATING PEMBAYARAN BEBAN ===");

        $bebanList = DB::table('pembayaran_beban')
            ->leftJoin('beban_operasional', 'pembayaran_beban.beban_operasional_id', '=', 'beban_operasional.id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('journal_entries')
                    ->whereColumn('journal_entries.ref_id', 'pembayaran_beban.id')
                    ->where('journal_entries.ref_type', 'pembayaran_beban');
            })
            ->select('pembayaran_beban.id', 'pembayaran_beban.tanggal', 'pembayaran_beban.jumlah', 'pembayaran_beban.keterangan', 'beban_operasional.nama_beban')
            ->get();

        $this->info("Found " . count($bebanList) . " pembayaran beban records to migrate");

        foreach ($bebanList as $beban) {
            $journalEntryId = DB::table('journal_entries')->insertGetId([
                'tanggal' => $beban->tanggal,
                'ref_type' => 'pembayaran_beban',
                'ref_id' => $beban->id,
                'memo' => 'Pembayaran Beban: ' . ($beban->keterangan ?: 'Tanpa catatan'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $coaBebanId = DB::table('coas')->where('kode_akun', '550')->value('id');
            $coaKasId = DB::table('coas')->where('kode_akun', '111')->value('id');

            // DEBIT
            DB::table('journal_lines')->insert([
                'journal_entry_id' => $journalEntryId,
                'coa_id' => $coaBebanId,
                'debit' => $beban->jumlah,
                'credit' => 0,
                'memo' => 'Pembayaran Beban',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // CREDIT
            DB::table('journal_lines')->insert([
                'journal_entry_id' => $journalEntryId,
                'coa_id' => $coaKasId,
                'debit' => 0,
                'credit' => $beban->jumlah,
                'memo' => 'Pembayaran Beban',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("✅ Migrated pembayaran beban ID: " . $beban->id);
        }

        $this->info("\n=== VERIFICATION ===");

        $penggajianCount = DB::table('journal_entries')->where('ref_type', 'penggajian')->count();
        $bebanCount = DB::table('journal_entries')->where('ref_type', 'pembayaran_beban')->count();

        $this->info("Penggajian in journal_entries: " . $penggajianCount);
        $this->info("Pembayaran Beban in journal_entries: " . $bebanCount);

        $this->info("\n✅ MIGRATION COMPLETE!");
        $this->info("Sekarang semua transaksi penggajian dan pembayaran beban akan muncul di jurnal umum!");
    }
}

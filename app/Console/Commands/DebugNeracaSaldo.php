<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Coa;
use App\Models\JurnalUmum;

class DebugNeracaSaldo extends Command
{
    protected $signature = 'debug:neraca-saldo {user_id} {--bulan=5} {--tahun=2026}';
    protected $description = 'Debug neraca saldo untuk menemukan penyebab ketidakseimbangan';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $bulan = $this->option('bulan');
        $tahun = $this->option('tahun');
        
        $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
        $endDate = date('Y-m-t', strtotime($startDate));

        $this->info("=== DEBUG NERACA SALDO ===");
        $this->info("User ID: $userId");
        $this->info("Periode: $startDate s/d $endDate");
        $this->newLine();

        // DEBUG 1: Cek Jurnal yang Tidak Seimbang
        $this->debugJurnalTidakSeimbang($userId);

        // DEBUG 2: Cek Duplikasi COA
        $this->debugDuplikasiCoa($userId);

        // DEBUG 3: Cek Total Debit vs Kredit
        $this->debugTotalDebitKredit($userId, $startDate, $endDate);

        // DEBUG 4: Cek Jurnal Duplikasi
        $this->debugJurnalDuplikasi($userId, $startDate, $endDate);

        // DEBUG 5: Cek Per Tipe Referensi
        $this->debugPerTipeReferensi($userId, $startDate, $endDate);

        // DEBUG 6: Cek Per Akun
        $this->debugPerAkun($userId, $startDate, $endDate);
    }

    /**
     * DEBUG 1: Cek Jurnal yang Tidak Seimbang
     */
    private function debugJurnalTidakSeimbang($userId)
    {
        $this->info("📋 DEBUG 1: Jurnal yang Tidak Seimbang");
        $this->line("─────────────────────────────────────────");

        $jurnalTidakSeimbang = DB::table('jurnal_umum')
            ->where('user_id', $userId)
            ->selectRaw('
                tipe_referensi,
                referensi,
                SUM(debit) as total_debit,
                SUM(kredit) as total_kredit,
                SUM(debit) - SUM(kredit) as selisih,
                COUNT(*) as jumlah_baris
            ')
            ->groupBy('tipe_referensi', 'referensi')
            ->havingRaw('ABS(SUM(debit) - SUM(kredit)) > 0.01')
            ->orderByRaw('ABS(SUM(debit) - SUM(kredit)) DESC')
            ->get();

        if ($jurnalTidakSeimbang->isEmpty()) {
            $this->line("✅ Semua jurnal seimbang (tidak ada masalah)");
        } else {
            $this->error("❌ Ditemukan " . count($jurnalTidakSeimbang) . " jurnal yang tidak seimbang:");
            $this->newLine();

            foreach ($jurnalTidakSeimbang as $jurnal) {
                $this->line("  Tipe: {$jurnal->tipe_referensi} | Ref: {$jurnal->referensi}");
                $this->line("  Debit: Rp " . number_format($jurnal->total_debit, 2, ',', '.'));
                $this->line("  Kredit: Rp " . number_format($jurnal->total_kredit, 2, ',', '.'));
                $this->error("  ⚠️  Selisih: Rp " . number_format(abs($jurnal->selisih), 2, ',', '.'));
                $this->line("  Jumlah Baris: {$jurnal->jumlah_baris}");
                $this->line("");
            }
        }

        $this->newLine();
    }

    /**
     * DEBUG 2: Cek Duplikasi COA
     */
    private function debugDuplikasiCoa($userId)
    {
        $this->info("📋 DEBUG 2: Duplikasi COA");
        $this->line("─────────────────────────────────────────");

        $duplikasiCoa = DB::table('coas')
            ->where('user_id', $userId)
            ->selectRaw('
                user_id,
                kode_akun,
                COUNT(*) as jumlah_duplikasi,
                GROUP_CONCAT(id) as coa_ids
            ')
            ->groupBy('user_id', 'kode_akun')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplikasiCoa->isEmpty()) {
            $this->line("✅ Tidak ada duplikasi COA");
        } else {
            $this->error("❌ Ditemukan " . count($duplikasiCoa) . " duplikasi COA:");
            $this->newLine();

            foreach ($duplikasiCoa as $dup) {
                $this->error("  Kode Akun: {$dup->kode_akun}");
                $this->line("  Jumlah Duplikasi: {$dup->jumlah_duplikasi}");
                $this->line("  COA IDs: {$dup->coa_ids}");
                $this->line("");
            }
        }

        $this->newLine();
    }

    /**
     * DEBUG 3: Cek Total Debit vs Kredit
     */
    private function debugTotalDebitKredit($userId, $startDate, $endDate)
    {
        $this->info("📋 DEBUG 3: Total Debit vs Kredit (Periode)");
        $this->line("─────────────────────────────────────────");

        $total = DB::table('jurnal_umum')
            ->where('user_id', $userId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('
                SUM(debit) as total_debit,
                SUM(kredit) as total_kredit,
                SUM(debit) - SUM(kredit) as selisih
            ')
            ->first();

        $this->line("Total Debit: Rp " . number_format($total->total_debit, 2, ',', '.'));
        $this->line("Total Kredit: Rp " . number_format($total->total_kredit, 2, ',', '.'));

        if (abs($total->selisih) > 0.01) {
            $this->error("❌ Selisih: Rp " . number_format(abs($total->selisih), 2, ',', '.'));
        } else {
            $this->line("✅ Selisih: Rp " . number_format(abs($total->selisih), 2, ',', '.'));
        }

        $this->newLine();
    }

    /**
     * DEBUG 4: Cek Jurnal Duplikasi
     */
    private function debugJurnalDuplikasi($userId, $startDate, $endDate)
    {
        $this->info("📋 DEBUG 4: Jurnal Duplikasi");
        $this->line("─────────────────────────────────────────");

        $duplikasi = DB::table('jurnal_umum')
            ->where('user_id', $userId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('
                tanggal,
                coa_id,
                debit,
                kredit,
                keterangan,
                COUNT(*) as jumlah_duplikasi,
                GROUP_CONCAT(id) as jurnal_ids
            ')
            ->groupBy('tanggal', 'coa_id', 'debit', 'kredit', 'keterangan')
            ->havingRaw('COUNT(*) > 1')
            ->orderByRaw('COUNT(*) DESC')
            ->get();

        if ($duplikasi->isEmpty()) {
            $this->line("✅ Tidak ada jurnal duplikasi");
        } else {
            $this->error("❌ Ditemukan " . count($duplikasi) . " jurnal duplikasi:");
            $this->newLine();

            foreach ($duplikasi as $dup) {
                $this->error("  Tanggal: {$dup->tanggal} | COA ID: {$dup->coa_id}");
                $this->line("  Debit: Rp " . number_format($dup->debit, 2, ',', '.'));
                $this->line("  Kredit: Rp " . number_format($dup->kredit, 2, ',', '.'));
                $this->line("  Keterangan: {$dup->keterangan}");
                $this->line("  Jumlah Duplikasi: {$dup->jumlah_duplikasi}");
                $this->line("  Jurnal IDs: {$dup->jurnal_ids}");
                $this->line("");
            }
        }

        $this->newLine();
    }

    /**
     * DEBUG 5: Cek Per Tipe Referensi
     */
    private function debugPerTipeReferensi($userId, $startDate, $endDate)
    {
        $this->info("📋 DEBUG 5: Total Debit/Kredit Per Tipe Referensi");
        $this->line("─────────────────────────────────────────");

        $perTipe = DB::table('jurnal_umum')
            ->where('user_id', $userId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('
                tipe_referensi,
                COUNT(*) as jumlah_jurnal,
                SUM(debit) as total_debit,
                SUM(kredit) as total_kredit,
                SUM(debit) - SUM(kredit) as selisih
            ')
            ->groupBy('tipe_referensi')
            ->orderByRaw('ABS(SUM(debit) - SUM(kredit)) DESC')
            ->get();

        foreach ($perTipe as $tipe) {
            $this->line("Tipe: {$tipe->tipe_referensi}");
            $this->line("  Jumlah Jurnal: {$tipe->jumlah_jurnal}");
            $this->line("  Debit: Rp " . number_format($tipe->total_debit, 2, ',', '.'));
            $this->line("  Kredit: Rp " . number_format($tipe->total_kredit, 2, ',', '.'));

            if (abs($tipe->selisih) > 0.01) {
                $this->error("  ⚠️  Selisih: Rp " . number_format(abs($tipe->selisih), 2, ',', '.'));
            } else {
                $this->line("  ✅ Selisih: Rp " . number_format(abs($tipe->selisih), 2, ',', '.'));
            }

            $this->line("");
        }

        $this->newLine();
    }

    /**
     * DEBUG 6: Cek Per Akun (Top 20 Akun dengan Selisih Terbesar)
     */
    private function debugPerAkun($userId, $startDate, $endDate)
    {
        $this->info("📋 DEBUG 6: Top 20 Akun dengan Selisih Terbesar");
        $this->line("─────────────────────────────────────────");

        $perAkun = DB::table('jurnal_umum as ju')
            ->join('coas as c', 'ju.coa_id', '=', 'c.id')
            ->where('ju.user_id', $userId)
            ->whereBetween('ju.tanggal', [$startDate, $endDate])
            ->selectRaw('
                c.kode_akun,
                c.nama_akun,
                c.saldo_normal,
                SUM(ju.debit) as total_debit,
                SUM(ju.kredit) as total_kredit,
                SUM(ju.debit) - SUM(ju.kredit) as selisih
            ')
            ->groupBy('c.id', 'c.kode_akun', 'c.nama_akun', 'c.saldo_normal')
            ->orderByRaw('ABS(SUM(ju.debit) - SUM(ju.kredit)) DESC')
            ->limit(20)
            ->get();

        foreach ($perAkun as $akun) {
            $this->line("{$akun->kode_akun} - {$akun->nama_akun}");
            $this->line("  Normal: {$akun->saldo_normal}");
            $this->line("  Debit: Rp " . number_format($akun->total_debit, 2, ',', '.'));
            $this->line("  Kredit: Rp " . number_format($akun->total_kredit, 2, ',', '.'));

            if (abs($akun->selisih) > 0.01) {
                $this->error("  ⚠️  Selisih: Rp " . number_format(abs($akun->selisih), 2, ',', '.'));
            } else {
                $this->line("  ✅ Selisih: Rp " . number_format(abs($akun->selisih), 2, ',', '.'));
            }

            $this->line("");
        }

        $this->newLine();
    }
}

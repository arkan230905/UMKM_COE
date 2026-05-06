<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penjualan;
use App\Models\JournalEntry;
use App\Services\JournalService;

class RebuildJurnalPenjualan extends Command
{
    protected $signature   = 'jurnal:rebuild-penjualan {id}';
    protected $description = 'Rebuild jurnal untuk satu transaksi penjualan';

    public function handle(): int
    {
        $id = $this->argument('id');
        $penjualan = Penjualan::with('details.produk', 'produk')->find($id);

        if (!$penjualan) {
            $this->error("Penjualan ID {$id} tidak ditemukan.");
            return 1;
        }

        // Login sebagai user penjualan atau user pertama admin
        $userId = $penjualan->user_id ?? \App\Models\User::where('role', 'owner')->first()?->id;
        if ($userId) {
            \Illuminate\Support\Facades\Auth::loginUsingId($userId);
        }

        $this->info("Rebuilding jurnal penjualan #{$penjualan->nomor_penjualan} (ID: {$id})...");

        try {
            JournalService::createJournalFromPenjualan($penjualan);
            $this->info("Berhasil!");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        // Tampilkan hasil
        $entry = JournalEntry::with('lines.coa')
            ->where('ref_type', 'sale')->where('ref_id', $id)->first();

        if ($entry) {
            $totalD = 0; $totalK = 0;
            $this->table(['Kode', 'Nama Akun', 'Debit', 'Kredit', 'Memo'], $entry->lines->map(function($l) use (&$totalD, &$totalK) {
                $totalD += $l->debit;
                $totalK += $l->credit;
                return [
                    $l->coa->kode_akun,
                    $l->coa->nama_akun,
                    $l->debit > 0 ? 'Rp '.number_format($l->debit,0,',','.') : '-',
                    $l->credit > 0 ? 'Rp '.number_format($l->credit,0,',','.') : '-',
                    $l->memo,
                ];
            })->toArray());

            $balanced = $totalD == $totalK;
            $this->info("Total Dr: Rp ".number_format($totalD,0,',','.')." | Cr: Rp ".number_format($totalK,0,',','.')." | ".($balanced ? "BALANCE ✓" : "TIDAK BALANCE ✗"));
        }

        return 0;
    }
}

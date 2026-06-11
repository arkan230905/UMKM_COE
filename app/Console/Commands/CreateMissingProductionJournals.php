<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produksi;
use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class CreateMissingProductionJournals extends Command
{
    protected $signature = 'produksi:create-missing-journals {--dry-run : Preview without creating journals}';
    protected $description = 'Create missing journal entries for completed productions that have no journals';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in DRY-RUN mode. No journals will be created.');
        }

        // Find completed productions without journal entries
        $completedProductions = Produksi::where('status', 'selesai')
            ->whereDoesntHave('jurnalUmum', function($query) {
                $query->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer']);
            })
            ->with('produk')
            ->orderBy('tanggal', 'desc')
            ->get();

        if ($completedProductions->isEmpty()) {
            $this->info('✅ All completed productions have journal entries. Nothing to do!');
            return 0;
        }

        $this->info("Found {$completedProductions->count()} production(s) without journal entries:");
        $this->newLine();

        foreach ($completedProductions as $produksi) {
            $this->line("ID: {$produksi->id}");
            $this->line("  Produk: {$produksi->produk->nama_produk}");
            $this->line("  Tanggal: {$produksi->tanggal->format('Y-m-d')}");
            $this->line("  Total Biaya: Rp " . number_format($produksi->total_biaya, 0, ',', '.'));

            if (!$dryRun) {
                try {
                    DB::transaction(function() use ($produksi) {
                        $this->createProductionJournals($produksi);
                    });
                    $this->info("  ✅ Journals created successfully!");
                } catch (\Exception $e) {
                    $this->error("  ❌ Error: {$e->getMessage()}");
                }
            } else {
                $this->comment("  Would create journal entries");
            }
            
            $this->newLine();
        }

        if (!$dryRun) {
            $this->info("Successfully processed {$completedProductions->count()} production(s)!");
        } else {
            $this->info("DRY-RUN complete. Run without --dry-run to create journals.");
        }

        return 0;
    }

    private function createProductionJournals($produksi)
    {
        $user_id = $produksi->user_id;
        $tanggal = $produksi->tanggal->format('Y-m-d');
        
        $totalBBB = $produksi->total_bahan;
        $totalBTKL = $produksi->total_btkl;
        $totalBOP = $produksi->total_bop;
        $totalHPP = $produksi->total_biaya;

        // Get HPP breakdown from saved details
        $hppData = $this->getHppFromDetails($produksi);

        // JURNAL 1: BBB → Pers. Barang Dalam Proses - BBB
        if ($totalBBB > 0) {
            // DEBIT: Pers. Barang Dalam Proses - BBB (1171)
            JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1171', $user_id),
                'tanggal' => $tanggal,
                'keterangan' => 'Konsumsi BBB untuk Produksi ' . $produksi->produk->nama_produk,
                'debit' => $totalBBB,
                'kredit' => 0,
                'referensi' => $produksi->id,
                'tipe_referensi' => 'produksi_bbb',
                'created_by' => $user_id,
            ]);

            // KREDIT: Setiap bahan baku
            foreach ($hppData['bbb'] as $bbb) {
                if ($bbb['subtotal'] > 0) {
                    JurnalUmum::create([
                        'user_id' => $user_id,
                        'coa_id' => $this->getCoaIdByKode('1141', $user_id),
                        'tanggal' => $tanggal,
                        'keterangan' => 'Konsumsi ' . $bbb['nama'] . ' untuk Produksi',
                        'debit' => 0,
                        'kredit' => $bbb['subtotal'],
                        'referensi' => $produksi->id,
                        'tipe_referensi' => 'produksi_bbb',
                        'created_by' => $user_id,
                    ]);
                }
            }
        }

        // JURNAL 2: BTKL → Pers. Barang Dalam Proses - BTKL
        if ($totalBTKL > 0) {
            // DEBIT: Pers. Barang Dalam Proses - BTKL (1172)
            JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1172', $user_id),
                'tanggal' => $tanggal,
                'keterangan' => 'Alokasi BTKL untuk Produksi ' . $produksi->produk->nama_produk,
                'debit' => $totalBTKL,
                'kredit' => 0,
                'referensi' => $produksi->id,
                'tipe_referensi' => 'produksi_btkl',
                'created_by' => $user_id,
            ]);

            // KREDIT: Hutang Gaji (211)
            JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('211', $user_id),
                'tanggal' => $tanggal,
                'keterangan' => 'Hutang Gaji untuk Produksi',
                'debit' => 0,
                'kredit' => $totalBTKL,
                'referensi' => $produksi->id,
                'tipe_referensi' => 'produksi_btkl',
                'created_by' => $user_id,
            ]);
        }

        // JURNAL 3: BOP → Pers. Barang Dalam Proses - BOP
        if ($totalBOP > 0) {
            // DEBIT: Barang Dalam Proses BOP (1173)
            JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $this->getCoaIdByKode('1173', $user_id),
                'tanggal' => $tanggal,
                'keterangan' => 'Alokasi BOP untuk Produksi ' . $produksi->produk->nama_produk,
                'debit' => $totalBOP,
                'kredit' => 0,
                'referensi' => $produksi->id,
                'tipe_referensi' => 'produksi_bop',
                'created_by' => $user_id,
            ]);

            // KREDIT: Per komponen BOP (Bahan Pendukung)
            foreach ($hppData['bop'] as $bop) {
                if ($bop['subtotal'] > 0) {
                    // Use COA from bahan pendukung or fallback
                    $coaId = $this->getCoaIdByKodeForUser('1142', $user_id) // Persediaan Bahan Pendukung
                           ?? $this->getCoaIdByKodeForUser('530', $user_id)  // BOP umum
                           ?? $this->getCoaIdByKode('530', $user_id);
                    
                    JurnalUmum::create([
                        'user_id' => $user_id,
                        'coa_id' => $coaId,
                        'tanggal' => $tanggal,
                        'keterangan' => 'BOP - ' . $bop['nama'],
                        'debit' => 0,
                        'kredit' => $bop['subtotal'],
                        'referensi' => $produksi->id,
                        'tipe_referensi' => 'produksi_bop',
                        'created_by' => $user_id,
                    ]);
                }
            }
        }

        // JURNAL 4: Transfer ke Barang Jadi
        if ($totalHPP > 0) {
            $coaBarangJadi = $produksi->coa_persediaan_barang_jadi_id ?? $this->getCoaIdByKode('1161', $user_id);

            // DEBIT: Pers. Barang Jadi
            JurnalUmum::create([
                'user_id' => $user_id,
                'coa_id' => $coaBarangJadi,
                'tanggal' => $tanggal,
                'keterangan' => 'Transfer WIP ke Barang Jadi - ' . $produksi->produk->nama_produk,
                'debit' => $totalHPP,
                'kredit' => 0,
                'referensi' => $produksi->id,
                'tipe_referensi' => 'produksi_transfer',
                'created_by' => $user_id,
            ]);

            // KREDIT: WIP accounts
            if ($totalBBB > 0) {
                JurnalUmum::create([
                    'user_id' => $user_id,
                    'coa_id' => $this->getCoaIdByKode('1171', $user_id),
                    'tanggal' => $tanggal,
                    'keterangan' => 'Transfer WIP BBB ke Barang Jadi',
                    'debit' => 0,
                    'kredit' => $totalBBB,
                    'referensi' => $produksi->id,
                    'tipe_referensi' => 'produksi_transfer',
                    'created_by' => $user_id,
                ]);
            }

            if ($totalBTKL > 0) {
                JurnalUmum::create([
                    'user_id' => $user_id,
                    'coa_id' => $this->getCoaIdByKode('1172', $user_id),
                    'tanggal' => $tanggal,
                    'keterangan' => 'Transfer WIP BTKL ke Barang Jadi',
                    'debit' => 0,
                    'kredit' => $totalBTKL,
                    'referensi' => $produksi->id,
                    'tipe_referensi' => 'produksi_transfer',
                    'created_by' => $user_id,
                ]);
            }

            if ($totalBOP > 0) {
                JurnalUmum::create([
                    'user_id' => $user_id,
                    'coa_id' => $this->getCoaIdByKode('1173', $user_id),
                    'tanggal' => $tanggal,
                    'keterangan' => 'Transfer WIP BOP ke Barang Jadi',
                    'debit' => 0,
                    'kredit' => $totalBOP,
                    'referensi' => $produksi->id,
                    'tipe_referensi' => 'produksi_transfer',
                    'created_by' => $user_id,
                ]);
            }
        }
    }

    private function getHppFromDetails($produksi)
    {
        $hppData = [
            'bbb' => [],
            'bop' => []
        ];

        // Get BBB from produksi_details
        $details = $produksi->details()->whereNotNull('bahan_baku_id')->get();
        foreach ($details as $detail) {
            if ($detail->bahanBaku) {
                $hppData['bbb'][] = [
                    'nama' => $detail->bahanBaku->nama_bahan,
                    'subtotal' => $detail->subtotal
                ];
            }
        }

        // Get BOP from produksi_details (bahan pendukung)
        $bopDetails = $produksi->details()->whereNotNull('bahan_pendukung_id')->get();
        foreach ($bopDetails as $detail) {
            if ($detail->bahanPendukung) {
                $hppData['bop'][] = [
                    'nama' => $detail->bahanPendukung->nama_bahan,
                    'subtotal' => $detail->subtotal
                ];
            }
        }

        return $hppData;
    }

    private function getCoaIdByKode($kodeAkun, $user_id)
    {
        $coa = Coa::where('kode_akun', $kodeAkun)
            ->where('user_id', $user_id)
            ->first();
        
        if (!$coa) {
            throw new \Exception("COA {$kodeAkun} tidak ditemukan untuk user {$user_id}");
        }
        
        return $coa->id;
    }

    private function getCoaIdByKodeForUser($kodeAkun, $user_id): ?int
    {
        $coa = Coa::where('kode_akun', $kodeAkun)
            ->where('user_id', $user_id)
            ->first();

        return $coa ? $coa->id : null;
    }
}

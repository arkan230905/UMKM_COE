<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aset;
use App\Models\JurnalUmum;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FixApril2026Depreciation extends Command
{
    protected $signature = 'depreciation:fix-april-2026 {--dry-run : Show what would be fixed without making changes} {--force : Skip confirmation prompts}';
    protected $description = 'Fix April 2026 depreciation journal entries to match actual asset depreciation values';

    // Data aktual yang benar
    private $correctData = [
        'Mesin Produksi' => [
            'monthly' => 1333333.00,
            'expense_coa' => '555',
            'accum_coa' => '126',
            'keywords' => ['Mesin', 'Produksi']
        ],
        'Peralatan Produksi' => [
            'monthly' => 659474.00,
            'expense_coa' => '553',
            'accum_coa' => '120',
            'keywords' => ['Peralatan', 'Produksi']
        ],
        'Kendaraan' => [
            'monthly' => 888889.00,
            'expense_coa' => '554',
            'accum_coa' => '124',
            'keywords' => ['Kendaraan']
        ]
    ];

    public function handle()
    {
        $this->info('=== PERBAIKAN PENYUSUTAN APRIL 2026 ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('MODE DRY RUN - Tidak ada perubahan yang akan disimpan');
            $this->newLine();
        }

        // Step 1: Analisis masalah
        $this->info('1. MENGANALISIS JURNAL PENYUSUTAN APRIL 2026...');
        $issues = $this->analyzeApril2026Journals();
        
        if (empty($issues)) {
            $this->info('✓ Tidak ada masalah ditemukan dengan jurnal penyusutan April 2026');
            return 0;
        }

        $this->displayIssues($issues);

        // Step 2: Konfirmasi
        if (!$force && !$dryRun) {
            if (!$this->confirm('Apakah Anda ingin melanjutkan perbaikan?')) {
                $this->info('Perbaikan dibatalkan.');
                return 0;
            }
        }

        // Step 3: Perbaikan
        $this->info('2. MEMULAI PERBAIKAN...');
        $this->newLine();

        $fixed = 0;
        $errors = 0;

        foreach ($this->correctData as $assetName => $data) {
            try {
                $result = $this->fixAssetDepreciation($assetName, $data, $dryRun);
                if ($result) {
                    $fixed++;
                    $this->info("✓ {$assetName} - DIPERBAIKI");
                } else {
                    $this->line("- {$assetName} - TIDAK PERLU DIPERBAIKI");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ {$assetName} - ERROR: " . $e->getMessage());
            }
        }

        // Step 4: Validasi hasil
        if (!$dryRun && $fixed > 0) {
            $this->info('3. MEMVALIDASI HASIL...');
            $this->validateResults();
        }

        // Summary
        $this->newLine();
        $this->info('=== RINGKASAN ===');
        $this->info("Diperbaiki: {$fixed}");
        if ($errors > 0) {
            $this->error("Error: {$errors}");
        }

        if ($dryRun && $fixed > 0) {
            $this->newLine();
            $this->warn('Untuk menerapkan perbaikan, jalankan command tanpa --dry-run');
        }

        return 0;
    }

    private function analyzeApril2026Journals(): array
    {
        $issues = [];
        $targetDate = '2026-04-30';

        foreach ($this->correctData as $assetName => $data) {
            // Cari jurnal penyusutan untuk aset ini
            $journals = JurnalUmum::where('tanggal', $targetDate)
                ->where('keterangan', 'like', '%Penyusutan%')
                ->where(function($query) use ($data) {
                    foreach ($data['keywords'] as $keyword) {
                        $query->orWhere('keterangan', 'like', "%{$keyword}%");
                    }
                })
                ->get();

            if ($journals->isEmpty()) {
                $issues[] = [
                    'asset' => $assetName,
                    'type' => 'missing',
                    'message' => 'Jurnal penyusutan tidak ditemukan',
                    'expected' => $data['monthly'],
                    'actual' => 0
                ];
                continue;
            }

            // Periksa nominal
            $debitJournal = $journals->where('debit', '>', 0)->first();
            if ($debitJournal) {
                $actualAmount = (float)$debitJournal->debit;
                $expectedAmount = $data['monthly'];
                
                if (abs($actualAmount - $expectedAmount) > 0.01) {
                    $issues[] = [
                        'asset' => $assetName,
                        'type' => 'amount_mismatch',
                        'message' => 'Nominal penyusutan tidak sesuai',
                        'expected' => $expectedAmount,
                        'actual' => $actualAmount,
                        'difference' => $actualAmount - $expectedAmount
                    ];
                }
            }
        }

        return $issues;
    }

    private function displayIssues(array $issues): void
    {
        $this->newLine();
        $this->warn('MASALAH DITEMUKAN:');
        $this->newLine();

        foreach ($issues as $issue) {
            $this->line("• {$issue['asset']}:");
            $this->line("  {$issue['message']}");
            $this->line("  Seharusnya: Rp " . number_format($issue['expected'], 0, ',', '.'));
            $this->line("  Saat ini: Rp " . number_format($issue['actual'], 0, ',', '.'));
            
            if (isset($issue['difference'])) {
                $this->line("  Selisih: Rp " . number_format(abs($issue['difference']), 0, ',', '.'));
            }
            
            $this->newLine();
        }
    }

    private function fixAssetDepreciation(string $assetName, array $data, bool $dryRun): bool
    {
        $targetDate = '2026-04-30';
        $needsFix = false;

        DB::beginTransaction();
        
        try {
            // 1. Hapus jurnal lama yang salah
            $oldJournals = JurnalUmum::where('tanggal', $targetDate)
                ->where('keterangan', 'like', '%Penyusutan%')
                ->where(function($query) use ($data) {
                    foreach ($data['keywords'] as $keyword) {
                        $query->orWhere('keterangan', 'like', "%{$keyword}%");
                    }
                })
                ->get();

            if ($oldJournals->isNotEmpty()) {
                $this->line("  Menghapus " . $oldJournals->count() . " jurnal lama...");
                
                if (!$dryRun) {
                    JurnalUmum::where('tanggal', $targetDate)
                        ->where('keterangan', 'like', '%Penyusutan%')
                        ->where(function($query) use ($data) {
                            foreach ($data['keywords'] as $keyword) {
                                $query->orWhere('keterangan', 'like', "%{$keyword}%");
                            }
                        })
                        ->delete();
                }
                
                $needsFix = true;
            }

            // 2. Pastikan COA ada
            $expenseCoa = Coa::where('kode_akun', $data['expense_coa'])->first();
            $accumCoa = Coa::where('kode_akun', $data['accum_coa'])->first();

            if (!$expenseCoa || !$accumCoa) {
                throw new \Exception("COA tidak ditemukan: {$data['expense_coa']} atau {$data['accum_coa']}");
            }

            // 3. Buat jurnal baru dengan nilai yang benar
            if ($needsFix && !$dryRun) {
                $reference = 'DEPR-202604-' . strtoupper(str_replace(' ', '', $assetName));
                $description = "Penyusutan Aset {$assetName} (GL) 2026-04";

                // Debit - Beban Penyusutan
                JurnalUmum::create([
                    'coa_id' => $expenseCoa->id,
                    'tanggal' => $targetDate,
                    'keterangan' => $description,
                    'debit' => $data['monthly'],
                    'kredit' => 0,
                    'referensi' => $reference,
                    'tipe_referensi' => 'depr'
                ]);

                // Kredit - Akumulasi Penyusutan
                JurnalUmum::create([
                    'coa_id' => $accumCoa->id,
                    'tanggal' => $targetDate,
                    'keterangan' => $description,
                    'debit' => 0,
                    'kredit' => $data['monthly'],
                    'referensi' => $reference,
                    'tipe_referensi' => 'depr'
                ]);

                $this->line("  Membuat jurnal baru: Rp " . number_format($data['monthly'], 0, ',', '.'));
            }

            // 4. Update data aset jika perlu
            $aset = Aset::where(function($query) use ($data) {
                foreach ($data['keywords'] as $keyword) {
                    $query->orWhere('nama_aset', 'like', "%{$keyword}%");
                }
            })->first();

            if ($aset) {
                $currentMonthly = (float)($aset->penyusutan_per_bulan ?? 0);
                
                if (abs($currentMonthly - $data['monthly']) > 0.01) {
                    if (!$dryRun) {
                        $aset->update([
                            'penyusutan_per_bulan' => $data['monthly'],
                            'penyusutan_per_tahun' => $data['monthly'] * 12
                        ]);
                    }
                    
                    $this->line("  Update penyusutan aset: Rp " . number_format($data['monthly'], 0, ',', '.'));
                    $needsFix = true;
                }

                // Update akumulasi penyusutan
                if (!$dryRun) {
                    $totalAccumulated = JurnalUmum::join('coa', 'jurnal_umum.coa_id', '=', 'coa.id')
                        ->where('coa.kode_akun', $data['expense_coa'])
                        ->where('jurnal_umum.keterangan', 'like', '%Penyusutan%')
                        ->where('jurnal_umum.debit', '>', 0)
                        ->sum('jurnal_umum.debit');

                    $aset->update(['akumulasi_penyusutan' => $totalAccumulated]);
                }
            }

            DB::commit();
            return $needsFix;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function validateResults(): void
    {
        $this->newLine();
        
        foreach ($this->correctData as $assetName => $data) {
            $journals = JurnalUmum::where('tanggal', '2026-04-30')
                ->where('keterangan', 'like', '%Penyusutan%')
                ->where(function($query) use ($data) {
                    foreach ($data['keywords'] as $keyword) {
                        $query->orWhere('keterangan', 'like', "%{$keyword}%");
                    }
                })
                ->where('debit', '>', 0)
                ->first();

            if ($journals && abs((float)$journals->debit - $data['monthly']) < 0.01) {
                $this->info("✓ {$assetName}: Rp " . number_format($journals->debit, 0, ',', '.'));
            } else {
                $this->error("✗ {$assetName}: Masih ada masalah");
            }
        }
    }
}
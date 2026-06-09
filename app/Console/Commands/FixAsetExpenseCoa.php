<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAsetExpenseCoa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:aset-expense-coa {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix aset expense COA yang salah menggunakan akun Akumulasi (12x) menjadi akun Beban (5xx)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== MEMPERBAIKI EXPENSE COA UNTUK ASET ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - Tidak ada perubahan yang akan disimpan');
            $this->newLine();
        }

        // Mapping yang benar dari Akumulasi ke Beban
        $mapping = [
            '126' => '555', // Akumulasi Penyusutan Mesin → Beban Penyusutan Mesin
            '120' => '553', // Akumulasi Penyusutan Peralatan → Beban Penyusutan Peralatan
            '124' => '554', // Akumulasi Penyusutan Kendaraan → Beban Penyusutan Kendaraan
            '122' => '552', // Akumulasi Penyusutan Gedung → Beban Penyusutan Gedung
        ];

        DB::beginTransaction();

        try {
            // ISSUE 1: Ambil aset yang menggunakan akun Akumulasi (12x) untuk expense_coa_id
            $asets = DB::table('asets')
                ->join('coas', 'asets.expense_coa_id', '=', 'coas.id')
                ->where('coas.kode_akun', 'like', '12%')
                ->select('asets.id', 'asets.nama_aset', 'asets.user_id', 'coas.kode_akun', 'coas.nama_akun', 'asets.expense_coa_id')
                ->get();

            // ISSUE 2: Ambil aset yang menggunakan COA dari user lain (multi-tenancy issue)
            $assetsWithWrongUserCoa = DB::table('asets')
                ->join('coas as expense_coa', 'asets.expense_coa_id', '=', 'expense_coa.id')
                ->whereColumn('asets.user_id', '!=', 'expense_coa.user_id')
                ->select('asets.id', 'asets.nama_aset', 'asets.user_id', 'expense_coa.kode_akun', 'expense_coa.nama_akun', 'expense_coa.user_id as coa_user_id')
                ->get();

            $totalIssues = $asets->count() + $assetsWithWrongUserCoa->count();

            if ($totalIssues === 0) {
                $this->info('✓ Tidak ada aset yang perlu diperbaiki. Semua expense COA sudah benar!');
                DB::rollBack();
                return Command::SUCCESS;
            }

            $this->info("Ditemukan {$totalIssues} aset yang perlu diperbaiki:");
            $this->newLine();

            $fixed = 0;
            $failed = 0;

            // FIX ISSUE 1: Aset menggunakan akun Akumulasi (12x) untuk expense
            if ($asets->count() > 0) {
                $this->warn("Issue 1: {$asets->count()} aset menggunakan akun Akumulasi (12x) untuk Beban");
                $this->newLine();

                foreach ($asets as $aset) {
                    $kodeAkunLama = $aset->kode_akun;
                    
                    if (isset($mapping[$kodeAkunLama])) {
                        $kodeAkunBaru = $mapping[$kodeAkunLama];
                        
                        // Cari COA yang baru (Beban Penyusutan) untuk user yang sama
                        $coaBaru = DB::table('coas')
                            ->where('kode_akun', $kodeAkunBaru)
                            ->where('user_id', $aset->user_id)
                            ->first();
                        
                        if ($coaBaru) {
                            if (!$dryRun) {
                                // Update expense_coa_id
                                DB::table('asets')
                                    ->where('id', $aset->id)
                                    ->update([
                                        'expense_coa_id' => $coaBaru->id,
                                        'updated_at' => now()
                                    ]);
                            }
                            
                            $this->line("✓ Aset: <info>{$aset->nama_aset}</info>");
                            $this->line("  SEBELUM: {$kodeAkunLama} - {$aset->nama_akun}");
                            $this->line("  SESUDAH: <comment>{$coaBaru->kode_akun} - {$coaBaru->nama_akun}</comment>");
                            $this->newLine();
                            
                            $fixed++;
                        } else {
                            $this->error("✗ COA dengan kode {$kodeAkunBaru} tidak ditemukan untuk aset '{$aset->nama_aset}'");
                            $this->newLine();
                            $failed++;
                        }
                    } else {
                        $this->warn("? Tidak ada mapping untuk kode akun {$kodeAkunLama}");
                        $this->newLine();
                        $failed++;
                    }
                }
            }

            // FIX ISSUE 2: Aset menggunakan COA dari user lain
            if ($assetsWithWrongUserCoa->count() > 0) {
                $this->warn("Issue 2: {$assetsWithWrongUserCoa->count()} aset menggunakan COA dari user lain (multi-tenancy issue)");
                $this->newLine();

                foreach ($assetsWithWrongUserCoa as $aset) {
                    // Deteksi jenis aset berdasarkan nama
                    $kodeCoaBeban = null;
                    $kodeCoaAkumulasi = null;
                    $kodeCoaAset = null;

                    if (stripos($aset->nama_aset, 'mesin') !== false) {
                        $kodeCoaBeban = '555'; $kodeCoaAkumulasi = '126'; $kodeCoaAset = '125';
                    } elseif (stripos($aset->nama_aset, 'peralatan') !== false) {
                        $kodeCoaBeban = '553'; $kodeCoaAkumulasi = '120'; $kodeCoaAset = '119';
                    } elseif (stripos($aset->nama_aset, 'kendaraan') !== false) {
                        $kodeCoaBeban = '554'; $kodeCoaAkumulasi = '124'; $kodeCoaAset = '123';
                    } elseif (stripos($aset->nama_aset, 'gedung') !== false) {
                        $kodeCoaBeban = '552'; $kodeCoaAkumulasi = '122'; $kodeCoaAset = '121';
                    }

                    if ($kodeCoaBeban) {
                        // Cari COA yang benar untuk user yang sama
                        $coaBeban = DB::table('coas')->where('user_id', $aset->user_id)->where('kode_akun', $kodeCoaBeban)->first();
                        $coaAkumulasi = DB::table('coas')->where('user_id', $aset->user_id)->where('kode_akun', $kodeCoaAkumulasi)->first();
                        $coaAset = DB::table('coas')->where('user_id', $aset->user_id)->where('kode_akun', $kodeCoaAset)->first();

                        if ($coaBeban && $coaAkumulasi && $coaAset) {
                            if (!$dryRun) {
                                DB::table('asets')->where('id', $aset->id)->update([
                                    'expense_coa_id' => $coaBeban->id,
                                    'accum_depr_coa_id' => $coaAkumulasi->id,
                                    'asset_coa_id' => $coaAset->id,
                                    'updated_at' => now()
                                ]);
                            }

                            $this->line("✓ Aset: <info>{$aset->nama_aset}</info> (User {$aset->user_id})");
                            $this->line("  SEBELUM: COA dari User {$aset->coa_user_id}");
                            $this->line("  SESUDAH: Expense {$coaBeban->kode_akun}, Accum {$coaAkumulasi->kode_akun}, Asset {$coaAset->kode_akun} (User {$aset->user_id})");
                            $this->newLine();

                            $fixed++;
                        } else {
                            $this->error("✗ COA tidak lengkap untuk aset '{$aset->nama_aset}' User {$aset->user_id}");
                            $this->newLine();
                            $failed++;
                        }
                    } else {
                        $this->warn("? Tidak bisa mendeteksi jenis aset '{$aset->nama_aset}'");
                        $this->newLine();
                        $failed++;
                    }
                }
            }

            if ($dryRun) {
                DB::rollBack();
                $this->newLine();
                $this->info('DRY RUN SELESAI - Tidak ada perubahan disimpan');
            } else {
                DB::commit();
                $this->newLine();
                $this->info("=== SELESAI! {$fixed} aset berhasil diperbaiki ===");
            }

            if ($failed > 0) {
                $this->warn("{$failed} aset gagal diperbaiki");
            }

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('ERROR: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

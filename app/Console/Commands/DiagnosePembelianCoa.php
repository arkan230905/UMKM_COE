<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;
use App\Models\Pembelian;

class DiagnosePembelianCoa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:pembelian-coa {--user-id=} {--pembelian-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose COA mapping issues for pembelian transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $pembelianId = $this->option('pembelian-id');
        
        if (!$userId) {
            $this->error('--user-id is required');
            return 1;
        }
        
        $this->info('=== DIAGNOSTIC REPORT: Pembelian COA Mapping ===');
        $this->info('User ID: ' . $userId);
        $this->newLine();
        
        // Check Bahan Baku
        $this->info('1. BAHAN BAKU COA MAPPING:');
        $bahanBakus = BahanBaku::where('user_id', $userId)->get();
        
        foreach ($bahanBakus as $bb) {
            $status = '❌ MISSING';
            $coaExists = false;
            $coaName = 'N/A';
            
            if ($bb->coa_persediaan_id) {
                $coa = Coa::where('kode_akun', $bb->coa_persediaan_id)
                    ->where('user_id', $userId)
                    ->first();
                
                if ($coa) {
                    $status = '✅ OK';
                    $coaExists = true;
                    $coaName = $coa->nama_akun;
                } else {
                    $status = '⚠️  COA CODE NOT FOUND';
                }
            }
            
            $this->line(sprintf(
                '  %s [ID: %d] %s - COA: %s (%s) - %s',
                $status,
                $bb->id,
                $bb->nama_bahan,
                $bb->coa_persediaan_id ?? 'NULL',
                $coaName,
                $bb->is_active ? 'Active' : 'Inactive'
            ));
        }
        
        $this->newLine();
        
        // Check Bahan Pendukung
        $this->info('2. BAHAN PENDUKUNG COA MAPPING:');
        $bahanPendukungs = BahanPendukung::where('user_id', $userId)->get();
        
        foreach ($bahanPendukungs as $bp) {
            $status = '❌ MISSING';
            $coaExists = false;
            $coaName = 'N/A';
            
            if ($bp->coa_persediaan_id) {
                $coa = Coa::where('kode_akun', $bp->coa_persediaan_id)
                    ->where('user_id', $userId)
                    ->first();
                
                if ($coa) {
                    $status = '✅ OK';
                    $coaExists = true;
                    $coaName = $coa->nama_akun;
                } else {
                    $status = '⚠️  COA CODE NOT FOUND';
                }
            }
            
            $this->line(sprintf(
                '  %s [ID: %d] %s - COA: %s (%s) - %s',
                $status,
                $bp->id,
                $bp->nama_bahan,
                $bp->coa_persediaan_id ?? 'NULL',
                $coaName,
                $bp->is_active ? 'Active' : 'Inactive'
            ));
        }
        
        $this->newLine();
        
        // Check specific pembelian if provided
        if ($pembelianId) {
            $this->info('3. SPECIFIC PEMBELIAN DETAILS:');
            $pembelian = Pembelian::with([
                'details.bahanBaku',
                'details.bahanPendukung'
            ])->find($pembelianId);
            
            if ($pembelian) {
                $this->line('  Pembelian: ' . $pembelian->nomor_pembelian);
                $this->line('  User ID: ' . $pembelian->user_id);
                $this->line('  Tanggal: ' . $pembelian->tanggal);
                $this->newLine();
                
                foreach ($pembelian->details as $detail) {
                    if ($detail->bahanBaku) {
                        $bb = $detail->bahanBaku;
                        $status = $bb->coa_persediaan_id ? '✅' : '❌';
                        
                        $coaCheck = 'N/A';
                        if ($bb->coa_persediaan_id) {
                            $coa = Coa::where('kode_akun', $bb->coa_persediaan_id)
                                ->where('user_id', $userId)
                                ->first();
                            $coaCheck = $coa ? '✅ Exists' : '❌ Not Found';
                        }
                        
                        $this->line(sprintf(
                            '  %s Bahan Baku: %s (ID: %d)',
                            $status,
                            $bb->nama_bahan,
                            $bb->id
                        ));
                        $this->line('     COA Persediaan ID: ' . ($bb->coa_persediaan_id ?? 'NULL'));
                        $this->line('     COA Check: ' . $coaCheck);
                    }
                    
                    if ($detail->bahanPendukung) {
                        $bp = $detail->bahanPendukung;
                        $status = $bp->coa_persediaan_id ? '✅' : '❌';
                        
                        $coaCheck = 'N/A';
                        if ($bp->coa_persediaan_id) {
                            $coa = Coa::where('kode_akun', $bp->coa_persediaan_id)
                                ->where('user_id', $userId)
                                ->first();
                            $coaCheck = $coa ? '✅ Exists' : '❌ Not Found';
                        }
                        
                        $this->line(sprintf(
                            '  %s Bahan Pendukung: %s (ID: %d)',
                            $status,
                            $bp->nama_bahan,
                            $bp->id
                        ));
                        $this->line('     COA Persediaan ID: ' . ($bp->coa_persediaan_id ?? 'NULL'));
                        $this->line('     COA Check: ' . $coaCheck);
                    }
                }
            } else {
                $this->error('  Pembelian not found with ID: ' . $pembelianId);
            }
            
            $this->newLine();
        }
        
        // Summary
        $this->info('4. SUMMARY:');
        $bbMissing = BahanBaku::where('user_id', $userId)
            ->whereNull('coa_persediaan_id')
            ->count();
        $bpMissing = BahanPendukung::where('user_id', $userId)
            ->whereNull('coa_persediaan_id')
            ->count();
        
        $this->line('  Bahan Baku Missing COA: ' . $bbMissing);
        $this->line('  Bahan Pendukung Missing COA: ' . $bpMissing);
        
        if ($bbMissing > 0 || $bpMissing > 0) {
            $this->newLine();
            $this->warn('⚠️  ACTION REQUIRED: Some items are missing COA Persediaan mapping.');
            $this->info('Please set COA Persediaan for each item in Master Data.');
        } else {
            $this->newLine();
            $this->info('✅ All items have COA Persediaan mapping!');
        }
        
        return 0;
    }
}

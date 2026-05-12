<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDepreciationJournal extends Command
{
    protected $signature = 'fix:depreciation-journal';
    protected $description = 'Fix depreciation journal values to match actual asset depreciation amounts';

    public function handle()
    {
        $this->info('=== CURRENT DEPRECIATION VALUES ===');
        
        $results = DB::table('jurnal_umum')
            ->select([
                'keterangan', 
                'debit', 
                'kredit',
                DB::raw("CASE 
                    WHEN keterangan LIKE '%Mesin%' THEN 'Mesin Produksi'
                    WHEN keterangan LIKE '%Peralatan%' THEN 'Peralatan Produksi'
                    WHEN keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
                    ELSE 'Lainnya'
                END as kategori")
            ])
            ->where('tanggal', '2026-04-30')
            ->where('keterangan', 'LIKE', '%Penyusutan%')
            ->orderBy('debit', 'desc')
            ->get();
        
        foreach ($results as $row) {
            $amount = max($row->debit, $row->kredit);
            $type = $row->debit > 0 ? 'Debit' : 'Kredit';
            $kategori = $row->kategori;
            
            $this->line("$kategori: $type Rp " . number_format($amount, 0, ',', '.'));
        }
        
        $this->info("\n=== FIXING VALUES ===");
        
        DB::beginTransaction();
        
        try {
            // Update Peralatan: 1333333 -> 659474
            $updated1 = DB::table('jurnal_umum')
                ->where('tanggal', '2026-04-30')
                ->where('keterangan', 'LIKE', '%Peralatan%')
                ->where('debit', 1333333.00)
                ->update(['debit' => 659474.00]);
            
            $updated2 = DB::table('jurnal_umum')
                ->where('tanggal', '2026-04-30')
                ->where('keterangan', 'LIKE', '%Peralatan%')
                ->where('kredit', 1333333.00)
                ->update(['kredit' => 659474.00]);
            
            $this->line("Peralatan - Debit: $updated1, Kredit: $updated2");
            
            // Update Kendaraan: 1333333 -> 888889
            $updated3 = DB::table('jurnal_umum')
                ->where('tanggal', '2026-04-30')
                ->where('keterangan', 'LIKE', '%Kendaraan%')
                ->where('debit', 1333333.00)
                ->update(['debit' => 888889.00]);
            
            $updated4 = DB::table('jurnal_umum')
                ->where('tanggal', '2026-04-30')
                ->where('keterangan', 'LIKE', '%Kendaraan%')
                ->where('kredit', 1333333.00)
                ->update(['kredit' => 888889.00]);
            
            $this->line("Kendaraan - Debit: $updated3, Kredit: $updated4");
            
            $totalUpdated = $updated1 + $updated2 + $updated3 + $updated4;
            
            if ($totalUpdated > 0) {
                DB::commit();
                $this->info("\n✅ SUCCESS! Total $totalUpdated rows updated.");
                
                // Validate final results
                $this->info("\n=== FINAL RESULTS ===");
                $results = DB::table('jurnal_umum')
                    ->select([
                        'keterangan', 
                        'debit', 
                        'kredit',
                        DB::raw("CASE 
                            WHEN keterangan LIKE '%Mesin%' THEN 'Mesin Produksi'
                            WHEN keterangan LIKE '%Peralatan%' THEN 'Peralatan Produksi'
                            WHEN keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
                            ELSE 'Lainnya'
                        END as kategori")
                    ])
                    ->where('tanggal', '2026-04-30')
                    ->where('keterangan', 'LIKE', '%Penyusutan%')
                    ->orderBy('debit', 'desc')
                    ->get();
                
                $expectedValues = [
                    'Mesin Produksi' => 1333333,
                    'Peralatan Produksi' => 659474,
                    'Kendaraan' => 888889
                ];
                
                foreach ($results as $row) {
                    $amount = max($row->debit, $row->kredit);
                    $type = $row->debit > 0 ? 'Debit' : 'Kredit';
                    $kategori = $row->kategori;
                    
                    $status = '❌';
                    if (isset($expectedValues[$kategori]) && $amount == $expectedValues[$kategori]) {
                        $status = '✅';
                    }
                    
                    $this->line("$status $kategori: $type Rp " . number_format($amount, 0, ',', '.'));
                }
                
                $this->info("\n🎉 Depreciation journal values have been corrected!");
                $this->info("Now refresh the jurnal umum page at /akuntansi/jurnal-umum to see the correct values.");
                
            } else {
                DB::rollback();
                $this->error("❌ No data updated.");
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
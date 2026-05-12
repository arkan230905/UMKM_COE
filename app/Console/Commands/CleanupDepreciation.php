<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDepreciation extends Command
{
    protected $signature = 'cleanup:depreciation';
    protected $description = 'Clean up old depreciation entries and insert correct values';

    public function handle()
    {
        $this->info('🔥 FINAL CLEANUP - REMOVING ALL OLD DEPRECIATION DATA');
        
        try {
            // Step 1: Check existing data
            $existing = DB::table('jurnal_umum')
                ->where('tanggal', '2026-04-30')
                ->where('keterangan', 'LIKE', '%Penyusutan%')
                ->count();
            
            $this->info("Existing depreciation entries: $existing");
            
            // Step 2: Delete ALL old depreciation entries
            DB::beginTransaction();
            
            $deleted = DB::table('jurnal_umum')
                ->where('tanggal', '2026-04-30')
                ->where(function($query) {
                    $query->where('keterangan', 'LIKE', '%Penyusutan%')
                          ->orWhere('keterangan', 'LIKE', '%GL) 2026-04%')
                          ->orWhere('keterangan', 'LIKE', '%SM) 2026-04%')
                          ->orWhere('keterangan', 'LIKE', '%SYD) 2026-04%');
                })
                ->delete();
            
            $this->info("Deleted entries: $deleted");
            
            // Step 3: Insert correct values
            $correctEntries = [
                // Mesin - Rp 1.333.333
                [
                    'coa_id' => 555,
                    'tanggal' => '2026-04-30',
                    'keterangan' => 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04',
                    'debit' => 1333333,
                    'kredit' => 0,
                    'referensi' => 'AST-MESIN',
                    'tipe_referensi' => 'depreciation',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'coa_id' => 126,
                    'tanggal' => '2026-04-30',
                    'keterangan' => 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04',
                    'debit' => 0,
                    'kredit' => 1333333,
                    'referensi' => 'AST-MESIN',
                    'tipe_referensi' => 'depreciation',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Peralatan - Rp 659.474 (CORRECTED)
                [
                    'coa_id' => 553,
                    'tanggal' => '2026-04-30',
                    'keterangan' => 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04',
                    'debit' => 659474,
                    'kredit' => 0,
                    'referensi' => 'AST-PERALATAN',
                    'tipe_referensi' => 'depreciation',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'coa_id' => 120,
                    'tanggal' => '2026-04-30',
                    'keterangan' => 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04',
                    'debit' => 0,
                    'kredit' => 659474,
                    'referensi' => 'AST-PERALATAN',
                    'tipe_referensi' => 'depreciation',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Kendaraan - Rp 888.889
                [
                    'coa_id' => 554,
                    'tanggal' => '2026-04-30',
                    'keterangan' => 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04',
                    'debit' => 888889,
                    'kredit' => 0,
                    'referensi' => 'AST-KENDARAAN',
                    'tipe_referensi' => 'depreciation',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'coa_id' => 124,
                    'tanggal' => '2026-04-30',
                    'keterangan' => 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04',
                    'debit' => 0,
                    'kredit' => 888889,
                    'referensi' => 'AST-KENDARAAN',
                    'tipe_referensi' => 'depreciation',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            DB::table('jurnal_umum')->insert($correctEntries);
            
            DB::commit();
            
            $this->info('✅ SUCCESS! Inserted ' . count($correctEntries) . ' correct entries');
            $this->info('✅ Mesin: Rp 1.333.333');
            $this->info('✅ Peralatan: Rp 659.474');
            $this->info('✅ Kendaraan: Rp 888.889');
            $this->info('✅ All old (GL), (SM), (SYD) entries REMOVED!');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ ERROR: ' . $e->getMessage());
        }
    }
}
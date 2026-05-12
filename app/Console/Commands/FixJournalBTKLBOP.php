<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixJournalBTKLBOP extends Command
{
    protected $signature = 'journal:fix-btkl-bop';
    protected $description = 'Fix BTKL & BOP journal entries - move from debit to credit';

    public function handle()
    {
        $this->info('Memperbaiki jurnal BTKL & BOP...');
        
        try {
            $updated = DB::update("
                UPDATE journal_lines jl
                INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                SET jl.credit = jl.debit, jl.debit = 0
                WHERE je.ref_type = 'production_labor_overhead'
                AND jl.coa_code IN ('52', '53')
                AND jl.debit > 0
            ");
            
            $this->info("✓ Berhasil memperbaiki {$updated} baris jurnal");
            
            // Show the fixed entries
            $entries = DB::select("
                SELECT 
                    je.tanggal,
                    je.memo,
                    jl.coa_code,
                    jl.debit,
                    jl.credit
                FROM journal_lines jl
                INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
                WHERE je.ref_type = 'production_labor_overhead'
                ORDER BY je.tanggal, jl.coa_code
            ");
            
            $this->table(
                ['Tanggal', 'Memo', 'Kode Akun', 'Debit', 'Kredit'],
                array_map(function($e) {
                    return [
                        $e->tanggal,
                        $e->memo,
                        $e->coa_code,
                        $e->debit,
                        $e->credit
                    ];
                }, $entries)
            );
            
            $this->info('Selesai!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}

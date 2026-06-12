<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckJournalLines extends Command
{
    protected $signature = 'check:journal-lines';
    protected $description = 'Check journal_lines data for debugging';

    public function handle()
    {
        $data = DB::table('journal_lines')
            ->where('tipe_referensi', 'adjustment_depreciation')
            ->select('id', 'user_id', 'tanggal', 'tipe_referensi', 'referensi', 'debit', 'credit')
            ->get();
        
        $this->info("Found {$data->count()} records:");
        
        foreach ($data as $row) {
            $this->line("ID: {$row->id}, User: {$row->user_id}, Tanggal: {$row->tanggal}, Ref: {$row->referensi}, Debit: {$row->debit}, Credit: {$row->credit}");
        }
        
        return 0;
    }
}

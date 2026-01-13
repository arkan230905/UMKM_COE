<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\JournalService;

class CreateReturnJournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-return-journal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create journal entries for return';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating Journal Entries for Return...');
        
        $journalService = new JournalService();
        
        try {
            // Create journal for return #5 (1 liter minyak goreng, Rp 20,000)
            $lines = [
                ['code' => '101', 'debit' => 20000.00, 'credit' => 0], // Kas (debit - uang masuk)
                ['code' => '1105', 'debit' => 0, 'credit' => 20000.00], // Persediaan Bahan Pendukung (credit - stock berkurang)
            ];
            
            $journal = $journalService->post(
                '2025-12-12',
                'purchase_return',
                5, // Return ID
                'Retur Pembelian - 1 LTR Minyak Goreng',
                $lines
            );
            
            $this->info('Journal entries created successfully!');
            $this->info('Journal ID: ' . $journal->id);
            $this->info('Entries:');
            $this->info('  101 (Kas): Debit Rp 20,000');
            $this->info('  1105 (Persediaan Bahan Pendukung): Credit Rp 20,000');
            
        } catch (\Exception $e) {
            $this->error('Error creating journal entries: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\JournalEntry;

class FindSaleUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find:sale-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find which user has sale journal entries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== FINDING USER WITH SALE JOURNAL ENTRIES ===");
        
        $users = User::all();
        $foundUser = null;
        
        foreach ($users as $user) {
            $saleCount = JournalEntry::where('user_id', $user->id)
                ->where('ref_type', 'sale')
                ->count();
                
            if ($saleCount > 0) {
                $this->info("User ID: {$user->id} ({$user->name}): {$saleCount} sale entries");
                $foundUser = $user;
                
                // Show details
                $saleEntries = JournalEntry::where('user_id', $user->id)
                    ->where('ref_type', 'sale')
                    ->get();
                    
                foreach ($saleEntries as $entry) {
                    $this->info("  Entry ID: {$entry->id}, Date: {$entry->tanggal}, Ref: {$entry->ref_id}, Memo: {$entry->memo}");
                }
            }
        }
        
        if (!$foundUser) {
            $this->info("No users found with sale journal entries");
        }
        
        $this->info("\n=== SEARCH COMPLETED ===");
        
        return Command::SUCCESS;
    }
}

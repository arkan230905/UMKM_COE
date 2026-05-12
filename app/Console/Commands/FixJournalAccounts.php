<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class FixJournalAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-journal-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix journal lines to use correct account codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing Journal Lines Account Codes...');
        
        DB::beginTransaction();
        try {
            // Cari akun baru yang benar
            $kasAccount = Account::where('code', '101')->first();
            $persediaanAccount = Account::where('code', '102')->first();
            
            // Update journal lines yang masih menggunakan kode lama
            if ($kasAccount) {
                $updated = JournalLine::whereHas('account', function($query) {
                    $query->where('code', '1101');
                })->update(['account_id' => $kasAccount->id]);
                
                $this->info("Updated {$updated} journal lines from 1101 to 101 (Kas)");
            }
            
            if ($persediaanAccount) {
                $updated = JournalLine::whereHas('account', function($query) {
                    $query->where('code', '1104');
                })->update(['account_id' => $persediaanAccount->id]);
                
                $this->info("Updated {$updated} journal lines from 1104 to 102 (Persediaan Bahan Baku)");
            }
            
            // Hapus akun lama yang tidak terpakai
            $oldKas = Account::where('code', '1101')->first();
            if ($oldKas) {
                $oldKas->delete();
                $this->info("Deleted old account 1101");
            }
            
            $oldPersediaan = Account::where('code', '1104')->first();
            if ($oldPersediaan) {
                $oldPersediaan->delete();
                $this->info("Deleted old account 1104");
            }
            
            DB::commit();
            $this->info('Journal accounts fixed successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Error fixing journal accounts: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

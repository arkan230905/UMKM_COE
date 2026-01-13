<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;

class CheckPersediaanAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-persediaan-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check persediaan accounts in COA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Persediaan Accounts...');
        
        // Check all persediaan accounts
        $persediaanAccounts = Coa::where('nama_akun', 'like', '%Persediaan%')
            ->orWhere('nama_akun', 'like', '%persediaan%')
            ->orderBy('kode_akun')
            ->get();
        
        $this->info('');
        $this->info('Found ' . $persediaanAccounts->count() . ' persediaan accounts:');
        
        foreach ($persediaanAccounts as $account) {
            $this->line('  Kode: ' . $account->kode_akun . ' - ' . $account->nama_akun);
            $this->line('    Tipe: ' . $account->tipe_akun);
            $this->line('    Header: ' . ($account->is_akun_header ? 'Yes' : 'No'));
            $this->line('    Saldo Awal: ' . ($account->saldo_awal ?? 0));
            $this->line('');
        }
        
        // Check current kas bank codes
        $this->info('Current Kas Bank Codes: ' . implode(', ', \App\Helpers\AccountHelper::KAS_BANK_CODES));
        
        // Check what accounts are currently returned
        $kasBankAccounts = \App\Helpers\AccountHelper::getKasBankAccounts();
        $this->info('');
        $this->info('Current Kas Bank Accounts:');
        foreach ($kasBankAccounts as $account) {
            $this->line('  Kode: ' . $account->kode_akun . ' - ' . $account->nama_akun);
        }
        
        return 0;
    }
}

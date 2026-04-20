<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;

class ValidatePayrollCoa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coa:validate-payroll 
                            {--create-missing : Create missing COA accounts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate that all required COA accounts for payroll exist';

    /**
     * Required COA accounts for payroll system
     */
    private $requiredAccounts = [
        '52' => [
            'nama_akun' => 'BIAYA TENAGA KERJA LANGSUNG (BTKL)',
            'tipe_akun' => 'Expense',
            'kategori_akun' => 'Expense',
            'saldo_normal' => 'debit'
        ],
        '54' => [
            'nama_akun' => 'BOP TENAGA KERJA TIDAK LANGSUNG',
            'tipe_akun' => 'Expense', 
            'kategori_akun' => 'Expense',
            'saldo_normal' => 'debit'
        ],
        '513' => [
            'nama_akun' => 'Beban Tunjangan',
            'tipe_akun' => 'Expense',
            'kategori_akun' => 'Expense', 
            'saldo_normal' => 'debit'
        ],
        '514' => [
            'nama_akun' => 'Beban Asuransi',
            'tipe_akun' => 'Expense',
            'kategori_akun' => 'Expense',
            'saldo_normal' => 'debit'
        ],
        '515' => [
            'nama_akun' => 'Beban Bonus',
            'tipe_akun' => 'Expense',
            'kategori_akun' => 'Expense',
            'saldo_normal' => 'debit'
        ],
        '516' => [
            'nama_akun' => 'Potongan Gaji',
            'tipe_akun' => 'Expense',
            'kategori_akun' => 'Expense',
            'saldo_normal' => 'kredit'
        ],
        '111' => [
            'nama_akun' => 'Kas Bank',
            'tipe_akun' => 'Asset',
            'kategori_akun' => 'Asset',
            'saldo_normal' => 'debit'
        ],
        '112' => [
            'nama_akun' => 'Kas',
            'tipe_akun' => 'Asset',
            'kategori_akun' => 'Asset',
            'saldo_normal' => 'debit'
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Validating Payroll COA Accounts...');
        $this->newLine();
        
        $missing = [];
        $existing = [];
        
        foreach ($this->requiredAccounts as $code => $accountData) {
            $coa = Coa::where('kode_akun', $code)->first();
            
            if ($coa) {
                $existing[] = $code;
                $this->line("✅ {$code} - {$coa->nama_akun}");
            } else {
                $missing[] = $code;
                $this->line("❌ {$code} - {$accountData['nama_akun']} (MISSING)");
            }
        }
        
        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("   Existing: " . count($existing) . "/" . count($this->requiredAccounts));
        $this->info("   Missing: " . count($missing));
        
        if (empty($missing)) {
            $this->info("🎉 All required payroll COA accounts exist!");
            return 0;
        }
        
        $this->warn("⚠️  Missing COA accounts found!");
        
        if ($this->option('create-missing')) {
            $this->createMissingAccounts($missing);
        } else {
            $this->newLine();
            $this->info("💡 To create missing accounts, run:");
            $this->line("   php artisan coa:validate-payroll --create-missing");
        }
        
        return count($missing) > 0 ? 1 : 0;
    }
    
    private function createMissingAccounts($missing)
    {
        $this->newLine();
        $this->info('🔧 Creating missing COA accounts...');
        
        foreach ($missing as $code) {
            $accountData = $this->requiredAccounts[$code];
            
            $coa = Coa::create([
                'kode_akun' => $code,
                'nama_akun' => $accountData['nama_akun'],
                'tipe_akun' => $accountData['tipe_akun'],
                'kategori_akun' => $accountData['kategori_akun'],
                'saldo_normal' => $accountData['saldo_normal'],
                'is_akun_header' => false,
                'saldo_awal' => 0,
                'tanggal_saldo_awal' => now(),
                'posted_saldo_awal' => false,
            ]);
            
            $this->line("✅ Created: {$code} - {$accountData['nama_akun']}");
        }
        
        $this->newLine();
        $this->info("🎉 All missing COA accounts have been created!");
        $this->info("💡 Don't forget to update your seeder:");
        $this->line("   php artisan coa:update-seeder --force");
    }
}
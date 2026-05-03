<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;

class CreateHPPCoa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:hpp-coa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create COA for Harga Pokok Penjualan';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== CREATING COA FOR HARGA POKOK PENJUALAN ===");
        
        // Check if COA already exists
        $existingCoa = Coa::where('nama_akun', 'Harga Pokok Penjualan')
            ->orWhere('kode_akun', '560')
            ->first();
            
        if ($existingCoa) {
            $this->info("❌ COA already exists:");
            $this->info("  Kode: {$existingCoa->kode_akun} - {$existingCoa->nama_akun}");
            return Command::FAILURE;
        }
        
        try {
            // Create new COA
            $hppCoa = new Coa();
            $hppCoa->kode_akun = '560';
            $hppCoa->nama_akun = 'Harga Pokok Penjualan';
            $hppCoa->tipe_akun = 'Expense';
            $hppCoa->saldo_normal = 'Debit';
            $hppCoa->level = 2;
            $hppCoa->parent_id = null; // Will be set later if needed
            $hppCoa->created_at = now();
            $hppCoa->updated_at = now();
            $hppCoa->save();
            
            $this->info("✅ COA created successfully:");
            $this->info("  Kode: {$hppCoa->kode_akun}");
            $this->info("  Nama: {$hppCoa->nama_akun}");
            $this->info("  Tipe: {$hppCoa->tipe_akun}");
            $this->info("  Saldo Normal: {$hppCoa->saldo_normal}");
            
            // Verify creation
            $verifyCoa = Coa::where('kode_akun', '560')->first();
            if ($verifyCoa) {
                $this->info("✅ Verification: COA exists in database");
            } else {
                $this->info("❌ Verification: COA not found in database");
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->info("❌ Error creating COA: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        $this->info("\n=== CREATION COMPLETED ===");
        $this->info("Now you can update JournalService to use COA 560 for HPP entries");
        
        return Command::SUCCESS;
    }
}

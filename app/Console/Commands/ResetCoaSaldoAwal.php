<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;

class ResetCoaSaldoAwal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coa:reset-saldo-awal {user_id : ID User/Tenant yang akan direset saldo awalnya}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset nilai saldo_awal menjadi 0 untuk semua COA milik user/tenant tertentu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        // Cari jumlah baris yang akan terdampak (yang bukan 0 atau null)
        $query = Coa::where('user_id', $userId)
                    ->whereNotNull('saldo_awal')
                    ->where('saldo_awal', '!=', 0);
                    
        $count = $query->count();

        if ($count === 0) {
            $this->info("Tidak ada data COA dengan saldo awal != 0 yang ditemukan untuk user_id: {$userId}.");
            return;
        }

        $this->warn("Ditemukan {$count} baris data COA dengan saldo_awal > 0 untuk user_id: {$userId}.");
        
        // Konfirmasi sebelum eksekusi
        if ($this->confirm("Apakah Anda yakin ingin mereset saldo_awal menjadi 0 untuk SEMUA ({$count}) data ini?", false)) {
            $updated = $query->update(['saldo_awal' => 0]);
            $this->info("Berhasil mereset {$updated} baris data COA.");
        } else {
            $this->info("Operasi dibatalkan.");
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LaporanKasBankController;
use Illuminate\Http\Request;

class TestCashBankReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-cash-bank-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test cash bank report functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Cash Bank Report...');
        
        // Create request with current month
        $request = new Request([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);
        
        $controller = new LaporanKasBankController();
        
        try {
            $result = $controller->index($request);
            
            // Extract data from view
            $data = $result->getData();
            $dataKasBank = $data['dataKasBank'] ?? [];
            
            $this->info('');
            $this->info('Cash Bank Report Results:');
            
            foreach ($dataKasBank as $data) {
                $this->line('  ' . $data['kode_akun'] . ' - ' . $data['nama_akun']);
                $this->line('    Saldo Awal: Rp ' . number_format($data['saldo_awal'], 2));
                $this->line('    Transaksi Masuk: Rp ' . number_format($data['transaksi_masuk'], 2));
                $this->line('    Transaksi Keluar: Rp ' . number_format($data['transaksi_keluar'], 2));
                $this->line('    Saldo Akhir: Rp ' . number_format($data['saldo_akhir'], 2));
                $this->line('');
            }
            
        } catch (\Exception $e) {
            $this->error('Error testing cash bank report: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PembayaranBeban;
use App\Models\BebanOperasional;

class DebugPembayaranBebanDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:pembayaran-beban-detail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug detailed pembayaran beban data and relationships';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== DEBUG DETAIL PEMBAYARAN BEBAN ===');
        
        // Get all pembayaran beban with detailed info
        $pembayaranBeban = PembayaranBeban::with(['coaBeban', 'coaKas', 'bebanOperasional'])->get();
        
        $this->info("\nDetail Pembayaran Beban:");
        foreach ($pembayaranBeban as $data) {
            $this->info("ID: {$data->id}");
            $this->info("User ID: {$data->user_id}");
            $this->info("Tanggal: {$data->tanggal}");
            $this->info("Jumlah: {$data->jumlah}");
            $this->info("Beban Operasional ID: " . ($data->beban_operasional_id ?? 'NULL'));
            $this->info("Beban Operasional Name: " . ($data->bebanOperasional->nama_beban ?? 'NULL'));
            $this->info("COA Beban ID: {$data->akun_beban_id}");
            $this->info("COA Beban Name: " . ($data->coaBeban->nama_akun ?? 'NULL'));
            $this->info("COA Kas ID: {$data->akun_kas_id}");
            $this->info("COA Kas Name: " . ($data->coaKas->nama_akun ?? 'NULL'));
            $this->info("Keterangan: {$data->keterangan}");
            $this->info("---");
        }
        
        // Check beban operasional by user
        $this->info("\nBeban Operasional by User:");
        $bebanOperasionalByUser = BebanOperasional::select('user_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->get();
        
        foreach ($bebanOperasionalByUser as $data) {
            $this->info("User ID {$data->user_id}: {$data->count} Beban Operasional records");
        }
        
        // Show beban operasional for user 4 (if exists)
        $this->info("\nBeban Operasional for User ID 4:");
        $bebanOperasionalUser4 = BebanOperasional::where('user_id', 4)->get();
        
        if ($bebanOperasionalUser4->isEmpty()) {
            $this->info("No Beban Operasional found for User ID 4");
        } else {
            foreach ($bebanOperasionalUser4 as $beban) {
                $this->info("ID: {$beban->id}, Name: {$beban->nama_beban}, Status: {$beban->status}, Budget: {$beban->budget_bulanan}");
            }
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BebanOperasional;
use App\Models\PembayaranBeban;

class DebugBebanOperasional extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:beban-operasional';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug beban operasional data and budget information';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== DEBUG BEBAN OPERASIONAL ===');
        
        // Check all beban operasional
        $allBebanOperasional = BebanOperasional::all();
        $this->info("\nAll Beban Operasional:");
        foreach ($allBebanOperasional as $beban) {
            $this->info("ID: {$beban->id}, User: {$beban->created_by}, Name: {$beban->nama_beban}, Budget: {$beban->budget_bulanan}, Status: {$beban->status}");
        }
        
        // Check beban operasional by user
        $this->info("\nBeban Operasional by User:");
        $bebanByUser = BebanOperasional::select('created_by', \DB::raw('COUNT(*) as count'), \DB::raw('SUM(budget_bulanan) as total_budget'))
            ->groupBy('created_by')
            ->get();
        
        foreach ($bebanByUser as $data) {
            $this->info("User ID {$data->created_by}: {$data->count} records, Total Budget: {$data->total_budget}");
        }
        
        // Check pembayaran beban and their beban operasional connections
        $this->info("\nPembayaran Beban and Beban Operasional Connections:");
        $pembayaranBeban = PembayaranBeban::with(['bebanOperasional'])->get();
        
        foreach ($pembayaranBeban as $pembayaran) {
            $this->info("Pembayaran ID {$pembayaran->id}: User {$pembayaran->user_id}");
            $this->info("  - Beban Operasional ID: " . ($pembayaran->beban_operasional_id ?? 'NULL'));
            $this->info("  - Beban Operasional Name: " . ($pembayaran->bebanOperasional->nama_beban ?? 'NULL'));
            $this->info("  - Beban Operasional Owner: " . ($pembayaran->bebanOperasional->created_by ?? 'NULL'));
            $this->info("  - Budget Available: " . ($pembayaran->bebanOperasional->budget_bulanan ?? 'NULL'));
            $this->info("---");
        }
        
        // Check if there are any beban operasional that could be used for user 4
        $this->info("\nPotential Beban Operasional for User 4:");
        $availableBeban = BebanOperasional::where('status', 'aktif')->get();
        $this->info("Total Active Beban Operasional: {$availableBeban->count()}");
        
        foreach ($availableBeban as $beban) {
            $this->info("ID: {$beban->id}, Name: {$beban->nama_beban}, Owner: {$beban->created_by}, Budget: {$beban->budget_bulanan}");
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}

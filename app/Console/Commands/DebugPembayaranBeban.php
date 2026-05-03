<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PembayaranBeban;

class DebugPembayaranBeban extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:pembayaran-beban';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug pembayaran beban data and user filtering';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== DEBUG PEMBAYARAN BEBAN ===');
        
        // Check total pembayaran beban
        $totalPembayaranBeban = PembayaranBeban::count();
        $this->info("Total Pembayaran Beban: {$totalPembayaranBeban}");
        
        // Check pembayaran beban by user_id
        $pembayaranByUser = PembayaranBeban::select('user_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->get();
        
        $this->info("\nPembayaran Beban by User ID:");
        foreach ($pembayaranByUser as $data) {
            $this->info("User ID {$data->user_id}: {$data->count} records");
        }
        
        // Show sample data
        $sampleData = PembayaranBeban::select('id', 'user_id', 'tanggal', 'jumlah', 'keterangan')
            ->take(5)
            ->get();
        
        $this->info("\nSample Pembayaran Beban Data:");
        foreach ($sampleData as $data) {
            $this->info("ID: {$data->id}, User: {$data->user_id}, Tanggal: {$data->tanggal}, Jumlah: {$data->jumlah}, Keterangan: {$data->keterangan}");
        }
        
        // Check current auth user (if any)
        if (auth()->check()) {
            $currentUserId = auth()->id();
            $userPembayaranCount = PembayaranBeban::where('user_id', $currentUserId)->count();
            $this->info("\nCurrent User ID: {$currentUserId}");
            $this->info("Pembayaran Beban for Current User: {$userPembayaranCount}");
        } else {
            $this->info("\nNo authenticated user found");
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}

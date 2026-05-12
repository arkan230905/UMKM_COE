<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penggajian;
use Illuminate\Support\Facades\DB;

class UpdatePenggajianStatus extends Command
{
    protected $signature = 'penggajian:update-status';
    protected $description = 'Update penggajian status to trigger journal creation';

    public function handle()
    {
        $this->info('Updating penggajian status to trigger journal creation...');
        
        try {
            DB::beginTransaction();
            
            // Get unpaid penggajian
            $unpaidPenggajian = Penggajian::where('status_pembayaran', 'belum_lunas')->get();
            
            $this->info("Found {$unpaidPenggajian->count()} unpaid penggajian records");
            
            foreach ($unpaidPenggajian as $penggajian) {
                $this->info("Processing Penggajian ID: {$penggajian->id}");
                
                // Update status - this will trigger the boot method
                $penggajian->status_pembayaran = 'lunas';
                $penggajian->tanggal_dibayar = $penggajian->tanggal_penggajian;
                $penggajian->save();
                
                $this->info("✓ Updated penggajian ID: {$penggajian->id}");
            }
            
            DB::commit();
            
            $this->info('All penggajian status updated successfully!');
            
            // Show summary
            $totalJurnal = \App\Models\JurnalUmum::where('tipe_referensi', 'penggajian')->count();
            $this->info("Total penggajian journal entries: {$totalJurnal}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
        }
    }
}
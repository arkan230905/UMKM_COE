<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugDirectQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:direct-query';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug using direct database queries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== DEBUG DIRECT DATABASE QUERY ===');
        
        // Query pembayaran beban directly
        $this->info("\n1. Pembayaran Beban (Direct Query):");
        $pembayaran = DB::table('pembayaran_beban')->where('id', 1)->first();
        if ($pembayaran) {
            $this->info("   ID: {$pembayaran->id}");
            $this->info("   User ID: {$pembayaran->user_id}");
            $this->info("   Beban Operasional ID: " . ($pembayaran->beban_operasional_id ?? 'NULL'));
            $this->info("   Jumlah: {$pembayaran->jumlah}");
        } else {
            $this->info("   No pembayaran beban found with ID 1");
        }
        
        // Query beban operasional directly
        $this->info("\n2. Beban Operasional (Direct Query):");
        $beban = DB::table('beban_operasional')->where('id', 1)->first();
        if ($beban) {
            $this->info("   ID: {$beban->id}");
            $this->info("   Created By: {$beban->created_by}");
            $this->info("   Name: {$beban->nama_beban}");
            $this->info("   Budget: {$beban->budget_bulanan}");
            $this->info("   Status: {$beban->status}");
        } else {
            $this->info("   No beban operasional found with ID 1");
        }
        
        // Check if there are any beban operasional records
        $this->info("\n3. All Beban Operasional Records:");
        $allBeban = DB::table('beban_operasional')->get();
        $this->info("   Total records: {$allBeban->count()}");
        foreach ($allBeban as $record) {
            $this->info("   ID: {$record->id}, Name: {$record->nama_beban}, Owner: {$record->created_by}");
        }
        
        // Check all pembayaran beban records
        $this->info("\n4. All Pembayaran Beban Records:");
        $allPembayaran = DB::table('pembayaran_beban')->get();
        $this->info("   Total records: {$allPembayaran->count()}");
        foreach ($allPembayaran as $record) {
            $this->info("   ID: {$record->id}, User: {$record->user_id}, Beban Op ID: " . ($record->beban_operasional_id ?? 'NULL'));
        }
        
        // Test the relationship manually
        $this->info("\n5. Manual Relationship Test:");
        $pembayaranWithBeban = DB::table('pembayaran_beban as pb')
            ->leftJoin('beban_operasional as bo', 'pb.beban_operasional_id', '=', 'bo.id')
            ->where('pb.id', 1)
            ->select('pb.*', 'bo.nama_beban', 'bo.created_by', 'bo.budget_bulanan')
            ->first();
            
        if ($pembayaranWithBeban) {
            $this->info("   Pembayaran ID: {$pembayaranWithBeban->id}");
            $this->info("   Beban Operasional Name: " . ($pembayaranWithBeban->nama_beban ?? 'NULL'));
            $this->info("   Beban Operasional Owner: " . ($pembayaranWithBeban->created_by ?? 'NULL'));
            $this->info("   Budget Available: " . ($pembayaranWithBeban->budget_bulanan ?? 'NULL'));
        } else {
            $this->info("   No joined record found");
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}

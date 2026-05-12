<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteBomJobCosting extends Command
{
    protected $signature = 'bom:delete-job-costing {produk_id}';
    protected $description = 'Delete BOM Job Costing for a product';

    public function handle()
    {
        $produkId = $this->argument('produk_id');
        
        // Delete main record only
        $deleted = DB::table('bom_job_costings')->where('produk_id', $produkId)->delete();
        
        $this->info("Deleted {$deleted} BOM Job Costing record(s) for product ID {$produkId}");
        
        return 0;
    }
}

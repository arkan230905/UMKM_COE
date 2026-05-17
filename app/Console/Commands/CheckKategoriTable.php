<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckKategoriTable extends Command
{
    protected $signature = 'check:kategori-table';
    protected $description = 'Check kategori_produks table structure';

    public function handle()
    {
        $this->info('=== KATEGORI_PRODUKS TABLE STRUCTURE ===');
        
        $columns = DB::select('DESCRIBE kategori_produks');
        
        foreach ($columns as $col) {
            $this->line("Field: {$col->Field}");
            $this->line("Type: {$col->Type}");
            $this->line("Null: {$col->Null}");
            $this->line("Key: {$col->Key}");
            $this->line("Default: {$col->Default}");
            $this->line("Extra: {$col->Extra}");
            $this->line('---');
        }
    }
}

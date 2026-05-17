<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckUsersTable extends Command
{
    protected $signature = 'check:users-table';
    protected $description = 'Check users table structure';

    public function handle()
    {
        $this->info('=== USERS TABLE STRUCTURE ===');
        
        $columns = DB::select('DESCRIBE users');
        
        foreach ($columns as $col) {
            $this->line("Field: {$col->Field}");
            $this->line("Type: {$col->Type}");
            $this->line("Null: {$col->Null}");
            $this->line("Key: {$col->Key}");
            $this->line("Default: {$col->Default}");
            $this->line("Extra: {$col->Extra}");
            $this->line('---');
        }
        
        $this->newLine();
        $this->info('=== CHECK FOR plain_password COLUMN ===');
        
        if (Schema::hasColumn('users', 'plain_password')) {
            $this->info('✓ plain_password column EXISTS');
        } else {
            $this->error('✗ plain_password column DOES NOT EXIST');
            $this->info('Need to add plain_password column to users table');
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DebugPelanggan extends Command
{
    protected $signature = 'debug:pelanggan';
    protected $description = 'Debug data pelanggan di database';

    public function handle()
    {
        $this->info('=== ALL PELANGGAN (role=pelanggan) ===');
        $allPelanggan = User::where('role', 'pelanggan')->get();
        $this->info('Total: ' . $allPelanggan->count());
        $this->newLine();

        foreach ($allPelanggan as $p) {
            $this->line("ID: {$p->id}");
            $this->line("Name: {$p->name}");
            $this->line("Email: {$p->email}");
            $this->line("Role: {$p->role}");
            $this->line("User ID: " . ($p->user_id ?? 'NULL'));
            $this->line("Created: {$p->created_at}");
            $this->line('---');
        }

        $this->newLine();
        $this->info('=== PELANGGAN WITH user_id = NULL (Should appear in master data) ===');
        $nullUserIdPelanggan = User::where('role', 'pelanggan')->whereNull('user_id')->get();
        $this->info('Total: ' . $nullUserIdPelanggan->count());
        $this->newLine();

        foreach ($nullUserIdPelanggan as $p) {
            $this->line("ID: {$p->id}");
            $this->line("Name: {$p->name}");
            $this->line("Email: {$p->email}");
            $this->line('---');
        }

        $this->newLine();
        $this->info('=== PELANGGAN WITH user_id != NULL (Should NOT appear in master data) ===');
        $withUserIdPelanggan = User::where('role', 'pelanggan')->whereNotNull('user_id')->get();
        $this->info('Total: ' . $withUserIdPelanggan->count());
        $this->newLine();

        foreach ($withUserIdPelanggan as $p) {
            $this->line("ID: {$p->id}");
            $this->line("Name: {$p->name}");
            $this->line("Email: {$p->email}");
            $this->line("User ID: {$p->user_id}");
            $this->line('---');
        }
    }
}

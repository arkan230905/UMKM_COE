<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CheckPelangganPassword extends Command
{
    protected $signature = 'check:pelanggan-password';
    protected $description = 'Check pelanggan password details';

    public function handle()
    {
        $this->info('=== CHECKING PELANGGAN PASSWORD ===');
        
        $pelanggans = User::where('role', 'pelanggan')
            ->get();
        
        foreach ($pelanggans as $p) {
            $this->line("ID: {$p->id}");
            $this->line("Name: {$p->name}");
            $this->line("Email: {$p->email}");
            $this->line("Plain Password: " . ($p->plain_password ?? 'NULL'));
            $this->line("Hashed Password: " . substr($p->password, 0, 50) . "...");
            
            // Test if plain_password matches the hash
            if ($p->plain_password && Hash::check($p->plain_password, $p->password)) {
                $this->info("✓ Plain password matches hash");
            } else {
                $this->error("✗ Plain password DOES NOT match hash");
            }
            
            $this->line('---');
        }
    }
}

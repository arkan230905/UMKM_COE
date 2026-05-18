<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdatePelangganPlainPassword extends Command
{
    protected $signature = 'update:pelanggan-plain-password';
    protected $description = 'Update plain_password for existing pelanggan users';

    public function handle()
    {
        $this->info('=== UPDATING PELANGGAN PLAIN PASSWORD ===');
        
        // Get all pelanggan with no plain_password
        $pelanggans = User::where('role', 'pelanggan')
            ->whereNull('plain_password')
            ->get();
        
        $this->info('Found ' . $pelanggans->count() . ' pelanggan without plain_password');
        
        if ($pelanggans->count() === 0) {
            $this->info('No pelanggan to update');
            return;
        }
        
        // For each pelanggan, generate a default plain password
        foreach ($pelanggans as $pelanggan) {
            // Generate a simple plain password based on their name
            $plainPassword = 'password123'; // Default password
            
            $pelanggan->update([
                'plain_password' => $plainPassword
            ]);
            
            $this->line("✓ Updated: {$pelanggan->name} ({$pelanggan->email})");
        }
        
        $this->info('✓ All pelanggan updated successfully!');
    }
}
